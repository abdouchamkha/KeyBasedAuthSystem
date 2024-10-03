<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerSub;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerSub\StoreCustomerSubRequest;
use App\Http\Resources\CustomerSub as ResourceCustomerSub;
use App\Http\Requests\CustomerSub\UpdateCustomerSubRequest;

class CustomerSubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ResourceCustomerSub::collection(CustomerSub::paginate(is_numeric($request->input('paginate'))?$request->input('paginate'):10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerSubRequest $request)
    {
        $validatedData = $request->validated();
        $permssions = json_encode([
            'can_add_setup_time' => $validatedData['can_add_setup_time'],
            'unlimited_key_freeze_times' => $validatedData['unlimited_key_freeze_times'],
            'unlimited_key_reset_times' => $validatedData['unlimited_key_reset_times'],
            'can_create_key_with_no_hwid' => $validatedData['can_create_key_with_no_hwid'],
        ]);
        $customer = Customer::whereId($validatedData['customer_id'])->firstOrfail();
        $product = Product::whereId($validatedData['product_id'])->firstOrfail();
        if($product->user_id != auth()->id()||$customer->owner_id != auth()->id()){
            return response()->json(['error'=>'Not authorized'],403);
        }
        $customerSub = CustomerSub::create([
            'permissions' => $permssions,
            'customer_id'=>$customer->id,
            'app_id'=> auth()->user()->selectedApp()->first()->id,
            'product_id'=>$product->id,
            'subscription_type'=> $validatedData['subscription_type']
        ]);
        // generate a new customer invite link
        return response()->json($customerSub,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerSub $customerSub)
    {
        if($customerSub->app_id!= auth()->user()->selectedApp()->first()->id){
            return response()->json(['error'=>'Not authorized'],403);
        }
        return new ResourceCustomerSub($customerSub);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerSubRequest $request, CustomerSub $customerSub)
    {
        $validatedData = $request->validated();
        $permssions = json_encode([
            'can_add_setup_time' => $validatedData['can_add_setup_time'],
            'unlimited_key_freeze_times' => $validatedData['unlimited_key_freeze_times'],
            'unlimited_key_reset_times' => $validatedData['unlimited_key_reset_times'],
            'can_create_key_with_no_hwid' => $validatedData['can_create_key_with_no_hwid'],
        ]);
        $customer = Customer::whereId($validatedData['customer_id'])->firstOrfail();
        $product = Product::whereId($validatedData['product_id'])->firstOrfail();
        if($product->user_id != auth()->id()||$customer->owner_id != auth()->id()){
            return response()->json(['error'=>'Not authorized'],403);
        }
        $customerSub->update([
            'permissions' => $permssions,
            'customer_id'=>$customer->id,
            'app_id'=> auth()->user()->selectedApp()->first()->id,
            'product_id'=>$product->id,
            'subscription_type'=> $validatedData['subscription_type']
        ]);
        return response()->json($customerSub,201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerSub $customerSub)
    {
        abort(404);
        // if($customerSub->app_id->id == auth()->user()->selectedApp()->id){
        //     return response()->json($customerSub->delete());
        // }else{
        //     return response()->json(['error'=>'Unauthorized to delete this customer'],401);
        // }
    }
}
