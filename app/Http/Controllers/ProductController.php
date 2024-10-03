<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Resources\Product as ResourceProduct;
use App\Http\Requests\Product\UpdateProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('user', 'app')->where('user_id', auth()->id());

        // Apply search filter if provided
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('email', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('app', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Apply status filter if provided
        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        // Apply product status filter if provided
        $product_status = $request->input('product_status');
        if ($product_status) {
            $query->where('product_status', $product_status);
        }

        // Sort the results
        $allowedSortColumns = ['id', 'name', 'status', 'product_status', 'created_at'];
        $sort = $request->input('sort');
        if ($sort) {
            $sortParams = explode(':', $sort);
            $sortBy = in_array($sortParams[0], $allowedSortColumns) ? $sortParams[0] : 'id';
            $sortDirection = strtolower($sortParams[1] ?? 'asc');
            $query->orderBy($sortBy, $sortDirection);
        }

        // Return paginated results
        return ResourceProduct::collection($query->paginate(is_numeric($request->input('paginate')) ? $request->input('paginate') : 10));
    }
    /**
     * get products without detials.
     */
    public function getProducts()
    {
        return Product::select(  'id','name')->where('user_id', auth()->id())->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        $product = Product::create([
            'app_id' => auth()->user()->selectedApp()->first()->id,
            'user_id' => auth()->id(),
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
            'product_status' => $validatedData['product_status'],
        ]);
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if ($product->app_id == auth()->user()->selectedApp()->first()->id) {
            return new ResourceProduct($product);
        } else {
            return response()->json(['error' => 'Unauthorized to view this product'], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return response()->json($product, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if (auth()->user()->selectedApp()->first()->owner_id == auth()->id()) {
            return response()->json($product->delete(), 201);
        } else {
            return response()->json(['error' => 'Unauthorized to delete this product'], 401);
        }
    }
}
