<?php

namespace App\Http\Controllers;

use App\CustomerType;
use Exception;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Resources\Customer as ResourceCustomer;
use App\Http\Requests\Customer\UpdateCustomerRequest;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['owner', 'customer', 'app'])
            ->where('owner_id', auth()->id());

            if ($search = $request->input('search')) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('customer', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('owner', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('app', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
                });
            }
            $status = $request->input('status');
        if ($status == 'joined' || $status == 'pending') {
            $query->where('invite_status', $status);
        }
            $status = $request->input('type');
        if ($status == 'reseller' || $status == 'rebrand') {
            $query->where('customer_type', $status);
        }
        return ResourceCustomer::collection(
            $query->paginate($request->input('paginate', 10))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            // get customer_id
            $customerId = User::whereEmail($request->validated()['customer_email'])->firstOrfail(['id']);
            $customer = Customer::create([
                'customer_type' => $request->validated()['customer_type']==true?CustomerType::REBRAND:CustomerType::RESELLER,
                'customer_id' => $customerId['id'],
                'owner_id' => auth()->id(),
                'app_id' => auth()->user()->selectedApp()->firstorFail()->id,
            ]);
            return response()->json($customer,201);
        } catch (Exception $e) {
            return response()->json(['success'   => false,
            'message'   => 'Server errors','error'=>$e->getMessage()],500);
        }
    }
    /**
     * Accept a customer  invite link.
     * TODO: implement it in the resource route as Post /invite/{customer_id}
     */
    public function setAsInvited(Customer $customer)
    {
        $customer->invite_uuid = null;
        $customer->invite_status= 'joined';
        return response()->json($customer->save(),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return new ResourceCustomer($customer->with(['owner', 'customer', 'app'])->firstOrfail());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request,Customer  $customer)
    {
        $customer->update($request->validated());
        return response(status:201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if($customer->owner_id == auth()->id()){
            $customer->delete();
            return response()->json(['message' => 'The customer has been deleted successfully!'],201);
        }else{
            return response()->json(['error'=>'Unauthorized to delete this customer'],401);
        }
    }

}
