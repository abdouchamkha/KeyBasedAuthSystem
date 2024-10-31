<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductDownload;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductDownload as ResourceProductDownload;
use App\Http\Requests\ProductDownload\StoreProductDownloadRequest;
use App\Http\Requests\ProductDownload\UpdateProductDownloadRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDownloadController extends Controller
{
    protected $disk;

    // Inject the FileUploadService using the constructor
    public function __construct()
    {
        // Choose the disk based on the environment (local or production)
        $this->disk = config('app.env') === 'production' ? 'DO' : 'local';
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductDownload::where('app_id', auth()->user()->selectedApp()->first()->id)->with(['products', 'uploadedBy', 'updatedBy', 'deletedBy','createdBy']);

        // Apply search filter if provided
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply status filter if provided
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Apply product status filter if provided
        if ($product_status = $request->input('product_status')) {
            $query->whereHas('products', function ($q) use ($product_status) {
                $q->where('product_status', $product_status);
            });
        }

        // info($query->get());
        // return $query->paginate(is_numeric($request->input('paginate')) ? $request->input('paginate') : 10);
        return ResourceProductDownload::collection(
            $query->paginate(is_numeric($request->input('paginate')) ? $request->input('paginate') : 10)
        );
    }

    /**
     * Upload large files as chunks and process them.
     */
    public function uploadChunk(Request $request)
    {
        // Validate chunk request data
        $validator = Validator::make($request->all(), [
            'fileId' => 'required|string',
            'all' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:32',
            'fileName' => 'required|string',
            'totalChunks' => 'required|integer',
            'products' => [
                'required_without:all',
                'array',
                // Custom rule to check if each product belongs to the authenticated user
                function ($attribute, $value, $fail) {
                    foreach ($value as $productId) {
                        $product = Product::where('id', $productId)
                            ->where('user_id', auth()->id())
                            ->exists();

                        if (!$product) {
                            $fail("Unauthorized to upload for product ID {$productId}");
                        }
                    }
                }
            ],
            'chunkIndex' => 'required|integer',
            'file' => 'required|file',
        ], [
            'all.boolean' => 'The Global upload field must be true or false.',
            'products.required_without' => 'The products field is required when Global upload is not selected.',
        ]);

        $fileId = $request->input('fileId');
        $fileName = $request->input('fileName');
        $totalChunks = $request->input('totalChunks');
        $chunkIndex = $request->input('chunkIndex');
        $file = $request->file('file');

        // Create a temporary directory to store chunks
        $tempDir = storage_path("app/chunks/{$fileId}");
        if (!File::isDirectory($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // Save the current chunk to the temporary directory
        $chunkPath = "{$tempDir}/chunk_{$chunkIndex}";
        $file->move($tempDir, "chunk_{$chunkIndex}");

        // Check if all chunks are uploaded
        $uploadedChunks = File::files($tempDir);
        if (count($uploadedChunks) == $totalChunks) {
            // All chunks are uploaded, merge them

            // Ensure the final directory exists
            $finalDir = storage_path('app/uploads');
            if (!File::isDirectory($finalDir)) {
                File::makeDirectory($finalDir, 0755, true);
            }

            $finalFilePath = "{$finalDir}/{$fileName}";
            $finalFile = fopen($finalFilePath, 'ab');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = "{$tempDir}/chunk_{$i}";
                $chunk = fopen($chunkPath, 'rb');
                while ($buffer = fread($chunk, 4096)) {
                    fwrite($finalFile, $buffer);
                }
                fclose($chunk);
            }

            fclose($finalFile);

            // Delete temporary chunks
            File::deleteDirectory($tempDir);

            // Process the final assembled file
            return $this->processAssembledFile($request, new HttpFile($finalFilePath));
        }

        return response()->json(
            [
                'message' => 'Chunk uploaded successfully',
                'chunkIndex' => $chunkIndex,
                'totalChunks' => $totalChunks,
            ],
            200,
        );
    }

    /**
     * Process the assembled file.
     */
    protected function processAssembledFile(Request $request, HttpFile $file)
    {
        // Validate the rest of the request data
        $validator = Validator::make($request->all(), [
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:32',
            'products' => 'required_without:all|array',
            'products.*' => 'integer|exists:products,id',
            'all' => 'boolean',
            'fileName' => 'required|string', // Ensure the original file name is provided
        ], [
            'all.boolean' => 'The Global upload field must be true or false.',
            'products.required_without' => 'The products field is required when Global upload is not selected.',
            'fileName.required' => 'The fileName field is required.',
        ]);

        if ($validator->fails()) {
            // Delete the temporary file if validation fails
            File::delete($file->getPathname());
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ], 400);
        }

        $validatedData = $validator->validated();
        $isGlobal = $validatedData['all'] ?? false;
        $products = $validatedData['products'] ?? [];
        $tags = $validatedData['tags'] ?? [];
        $originalName = $validatedData['fileName'];
        $fileName = Str::random(40) . '_' . time(); // Randomized name with timestamp

        // Determine the storage path based on the type of file
        $storagePath = $isGlobal ? 'downloads/user-files/global' : 'downloads/user-files';

        // Upload the file to storage
        $storedFilePath = Storage::disk($this->disk)->putFileAs($storagePath, $file, $fileName);

        // Prepare labels (including original name and size)
        $labelsData = [
            'original_name' => encrypt($originalName),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ];

        // Create a single ProductDownload entry
        $productDownload = ProductDownload::create([
            'product_id'=>$isGlobal ?1 : 0,
            'path' => $storedFilePath,
            'name' => $fileName,
            'file_extension' => $file->extension(),
            'labels' => $labelsData,
            'tags' => $tags,
            'updated_by'=>auth()->id(),
            'created_by'=>auth()->id(),
            'app_id'=> auth()->user()->selectedApp()->first()->id,
        ]);

        // Attach the products to the ProductDownload, avoiding duplicates
        if (!$isGlobal && !empty($products)) {
            $validProducts = Product::whereIn('id', $products)
                ->where('user_id', auth()->id())
                ->pluck('id')
                ->toArray();

            $productDownload->products()->syncWithoutDetaching($validProducts);
        }

        // Delete the temporary assembled file
        File::delete($file->getPathname());

        return response()->json([
            'success' => true,
            'file' => $productDownload,
        ], 201);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductDownloadRequest $request)
    {
        abort(404);
        // $validatedData = $request->validated();
        // $isGlobal = $validatedData['all'] ?? false;

        // if (!$isGlobal) {
        //     $products = $validatedData['products']; // array of product IDs
        // } else {
        //     $products = [null]; // single entry with null product_id
        // }

        // $tags = $validatedData['tags'] ?? [];

        // $downloads = [];
        // foreach ($request->file('files') as $file) {
        //     $originalName = $file->getClientOriginalName(); // Get the original file name
        //     $fileName = Str::random(40) . '_' . time(); // Randomized name with timestamp

        //     foreach ($products as $productId) {
        //         // Only proceed if productId is valid
        //         if (!$isGlobal) {
        //             $product = Product::where('id', $productId)->where('user_id', auth()->id())->first();

        //             if (!$product) {
        //                 return response()->json(['error' => "Unauthorized to store download for product ID {$productId}"], 401);
        //             }
        //         }
        //         // Determine the storage path
        //         $storagePath = $isGlobal ? 'downloads/user-files/global' : "downloads/user-files/{$productId}";

        //         // Upload the file
        //         $filePath = Storage::disk($this->disk)->putFileAs($storagePath, $file, $fileName);

        //         // Prepare labels (including original name and size)
        //         $labelsData = [
        //             'original_name' => encrypt($originalName),
        //             'size' => $file->getSize(),
        //             'uploaded_at' => now(),
        //         ];

        //         $productDownload = ProductDownload::create([
        //             'path' => $filePath,
        //             'name' => $fileName,
        //             'product_id' => $productId, // null if global
        //             'file_extension' => $file->getClientOriginalExtension(),
        //             'labels' => json_encode($labelsData),
        //             'tags' => json_encode($tags),
        //         ]);

        //         $downloads[] = $productDownload;
        //     }
        // }

        // return response()->json($downloads, 201);
    }
    /**
     * Download a file from the storage.
     */
    public function download(ProductDownload $productDownload)
    {
        $product = Product::where('id', $productDownload->product_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Unauthorized to download this file'], 401);
        }

        // Determine the storage disk based on the environment

        // Check if the file exists on the disk
        if (!Storage::disk($this->disk)->exists($productDownload->path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get the file's content
        $fileContent = Storage::disk($this->disk)->get($productDownload->path);

        // Retrieve the original file name from labels
        $labels = json_decode($productDownload->labels, true);
        $originalName = decrypt($labels['original_name']) ?? 'downloaded_file';
        // Prepare the response with the correct headers
        return response($fileContent)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $originalName . '"');
    }
    /**
     * Download multipe fils
     */
    public function downloadMultiple(Request $request)
    {
        $validated = $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:product_downloads,id',
        ]);

        $downloads = ProductDownload::whereIn('id', $validated['file_ids'])
            ->whereHas('products', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        if ($downloads->isEmpty()) {
            return response()->json(['error' => 'No authorized files found to download'], 404);
        }

        $downloadLinks = [];
        foreach ($downloads as $download) {
            $path = $download->path;

            // Check if the file exists on the selected disk
            if (Storage::disk($this->disk)->exists($path)) {
                $labels = json_decode($download->labels, true);
                $originalName = $labels['original_name'] ?? 'file_' . $download->id;

                // Generate a temporary download URL (valid for a limited time)
                $temporaryUrl = Storage::disk($this->disk)->temporaryUrl(
                    $path,
                    now()->addMinutes(30), // URL valid for 30 minutes
                    ['ResponseContentDisposition' => "attachment; filename=\"{$originalName}\""],
                );

                $downloadLinks[] = [
                    'file_id' => $download->id,
                    'name' => $originalName,
                    'url' => $temporaryUrl,
                ];
            }
        }

        return response()->json($downloadLinks, 200);
    }
    /**
     * Display the specified resource.
     */
    public function show(ProductDownload $productDownload)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductDownloadRequest $request, ProductDownload $productDownload)
    {
        $validatedData = $request->validated();
        $product = Product::where('id', $validatedData['product_id'])
            ->where('user_id', auth()->id())
            ->first();
        if (!$product) {
            return response()->json(['error' => 'Unauthorized to update this product download'], 401);
        }

        // Delete the old file if it exists
        if (Storage::disk('private')->exists($productDownload->path)) {
            Storage::disk('private')->delete($productDownload->path);
        }

        // Handle the new file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = Str::random(40);
            $encryptedContent = encrypt(file_get_contents($file->getRealPath()));
            $filePath = Storage::disk('private')->put("downloads/$product->id/$fileName", $encryptedContent);

            // Update the ProductDownload record with new details
            $productDownload->update([
                'type' => $validatedData['type'],
                'path' => $filePath,
                'product_id' => $validatedData['product_id'],
                'file_extension' => $file->getClientOriginalExtension(),
            ]);

            return response()->json($productDownload, 200);
        }

        return response()->json(['error' => 'File not found'], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductDownload $productDownload)
    {
        $product = Product::where('id', $productDownload->product_id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$product) {
            return response()->json(['error' => 'Unauthorized to destroy this product download'], 401);
        } else {
            // Delete the old file if it exists
            if (Storage::disk('private')->exists($productDownload->path)) {
                Storage::disk('private')->delete($productDownload->path);
            }
            $productDownload->delete();
            return response()->json(['message' => 'Product download deleted successfully'], 200);
        }
    }
}
