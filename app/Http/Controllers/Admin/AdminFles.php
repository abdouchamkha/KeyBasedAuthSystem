<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductDownload;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminFles extends Controller
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
    $query = ProductDownload::with('products', 'uploadedBy', 'updatedBy');

    // Search for the name of the ProductDownload or related products
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('name', 'like', '%' . $search . '%')
            ->orWhereHas('products', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhereJsonContains('labels->original_name', $search);
            });
    }

    // Sort the results if sort and direction are provided
    if ($request->filled('sort') && $request->filled('direction')) {
        $query->orderBy($request->sort, $request->direction);
    }
    $loaders = $query->latest()->paginate(20)->withQueryString();
    return Inertia::render('Files/Index', [
        'files' => $loaders,
        'filters' => $request->only(['search', 'sort', 'direction']),
        'products'=>Product::where('user_id',auth()->user()->id)->get()
    ]);
}



    /**
     * Show the form for creating a new resource.
     */
    public function createProduct(Request $request)
{
    $validatedData = $request->validate([
        'status' => 'required|boolean',
        'product_status' => 'nullable|string|max:15',
        'name' => 'required|string|max:255',
    ]);
    $product = Product::create([
        'app_id' => auth()->user()->selectedApp()->first()->id,
        'user_id' => auth()->id(),
        'name' => $validatedData['name'],
        'status' => $validatedData['status'] ? 'active' : 'inactive', // Convert boolean to meaningful string
        'product_status' => $validatedData['product_status'] ?? 'Undefined',
    ]);

    return response()->json(['product' => $product], 201);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'required|file',
            'product' => 'required|exists:products,id',
        ]);
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::random(40) . '_' . time();
        $productId = $validatedData['product'];

        $product = Product::where('id', $productId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$product) {
            return response()->json(['error' => "Unauthorized to store download for product ID {$productId}"], 403);
        }

        $storagePath = "downloads/user-files/{$productId}";
        $filePath = Storage::disk($this->disk)->putFileAs($storagePath, $file, $fileName);

        $labelsData = [
            'original_name' => encrypt($originalName),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ];

        $productDownload = ProductDownload::create([
            'path' => $filePath,
            'name' => $fileName,
            'product_id' => $productId,
            'file_extension' => $file->getClientOriginalExtension(),
            'labels' => json_encode($labelsData),
            'tags' => json_encode(['uploaded_from_admin_panel']),
            'updated_by'=>auth()->id(),
            'created_by'=>auth()->id(),
            'app_id'=>auth()->user()->selectedApp()->first()->id,
        ]);

        return response()->json(['file' => $productDownload], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $validatedData = $request->validate([
        'file' => 'required|file',
    ]);

    // Find the existing ProductDownload
    $productDownload = ProductDownload::findOrFail($id);

    // Retrieve the uploaded file
    $file = $request->file('file');
    $originalName = $file->getClientOriginalName();
    $fileName = Str::random(40) . '_' . time(); // Generate a new file name
    $storagePath = "downloads/user-files/{$productDownload->product_id}";

    // Save the new file
    $filePath = Storage::disk(config('app.env') === 'production' ? 'DO' : 'local')
        ->putFileAs($storagePath, $file, $fileName);

    // Delete the old file if exists
    if ($productDownload->path) {
        Storage::disk(config('app.env') === 'production' ? 'DO' : 'local')
            ->delete($productDownload->path);
    }

    // Update the database record
    $productDownload->update([
        'path' => $filePath,
        'name' => $fileName,
        'file_extension' => $file->getClientOriginalExtension(),
        'labels' => json_encode([
            'original_name' => encrypt($originalName),
            'size' => $file->getSize(),
            'uploaded_at' => now(),
        ]),
        'updated_by'=>auth()->id(),
        'app_id'=>auth()->user()->selectedApp()->first()->id,
        'tags' => json_encode(['updated_from_admin_panel']), // Add new tag for auditing purposes
    ]);

    return response()->json(['message' => 'File updated successfully', 'file' => $productDownload], 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
