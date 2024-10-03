<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ProductDownload;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductDownload\StoreProductDownloadRequest;
use App\Http\Requests\ProductDownload\UpdateProductDownloadRequest;
use App\Http\Resources\ProductDownload as ResourceProductDownload;


class ProductDownloadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort(404);
        // $query = ProductDownload::with('product_id')->where('owner_id', auth()->id());
        // // Apply search filter if provided
        // $search = $request->input('search');
        // if ($search) {
        //     $query->where('name', 'like', "%{$search}%");
        // }

        // // Apply status filter if provided
        // $status = $request->input('status');
        // if ($status) {
        //     $query->where('status', $status);
        // }
        // // Apply product status filter if provided
        // $product_status = $request->input('product_status');
        // if ($status) {
        //     $query->where('product_status', $product_status);
        // }
        // return new ResourceProductDownload($query->paginate(is_numeric($request->input('paginate')) ? $request->input('paginate') : 10));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductDownloadRequest $request)
    {
        $validatedData = $request->validated();
        $product = Product::where('id', $validatedData['product_id'])
            ->where('user_id', auth()->id())
            ->first();
        if (!$product) {
            return response()->json(['error' => 'Unauthorized to store this product download'], 401);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = Str::random(40);
            $encryptedContent = encrypt(file_get_contents($file->getRealPath()));
            $filePath = Storage::disk('private')->put("downloads/$product->id/$fileName", $encryptedContent);

            $productDownload = ProductDownload::create([
                'type' => $validatedData['type'],
                'path' => $filePath,
                'product_id' => $validatedData['product_id'],
                'file_extension' => $file->getClientOriginalExtension(),
            ]);

            return response()->json($productDownload, 201);
        }

        return response()->json(['error' => 'File not found'], 400);
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
