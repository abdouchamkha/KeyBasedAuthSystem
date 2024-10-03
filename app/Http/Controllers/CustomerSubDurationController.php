<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Application;
use App\Models\CustomerSub;
use Illuminate\Http\Request;
use App\Models\CustomerSubDuration;
use App\Http\Requests\CustomerSubDuration\StoreCustomerSubDurationRequest;
use App\Http\Resources\CustomerSubDuration as ResourceCustomerSubDuration;
use App\Http\Requests\CustomerSubDuration\UpdateCustomerSubDurationRequest;

class CustomerSubDurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerSubDurationRequest $request)
    {
        $validatedData = $request->validated();

        // Find the CustomerSub record and check authorization
        $customerSub = CustomerSub::whereId($validatedData['subscripton_id'])->firstOrFail();
        if ($customerSub->app_id != auth()->user()->selectedApp()->id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        // Prepare the data with timestamp conversion
        $customerSubDuration = CustomerSubDuration::create([
            'subscripton_id' => $validatedData['subscripton_id'],
            'started_at' => $customerSub->subscription_type == 'days_system'
                ? null
                : Carbon::parse($validatedData['started_at'])->timestamp,
            'end_at' => $customerSub->subscription_type == 'days_system'
                ? null
                : Carbon::parse($validatedData['end_at'])->timestamp,
            'days_left' => $customerSub->subscription_type == 'days_system'
                ? $validatedData['days_left']
                : null
        ]);

        return response()->json($customerSubDuration, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerSubDuration $customerSubDuration)
    {
        if($customerSubDuration->subscripton_id->app_id!= auth()->user()->selectedApp()->id){
            return response()->json(['error'=>'Not authorized'],403);
        }
        return new ResourceCustomerSubDuration($customerSubDuration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerSubDurationRequest $request, CustomerSubDuration $customerSubDuration)
    {
        if($customerSubDuration->subscripton_id->app_id== auth()->user()->selectedApp()->id){
            $applications = Application::whereId($customerSubDuration->subscripton_id->app_id)->firstOrfail();
            if($applications->owner_id != auth()->id()){
                return response()->json(['error'=>'Not authorized'],403);
            }
            $validatedData = $request->validated();
            $updateData = [];
            // Prepare only the data that is not null
            if (isset($validatedData['subscripton_id'])) {
                $updateData['subscripton_id'] = $validatedData['subscripton_id'];
            }
            if (isset($validatedData['started_at'])) {
                $updateData['started_at'] = $customerSubDuration->customerSub->subscription_type == 'days_system'
                    ? null
                    : Carbon::parse($validatedData['started_at'])->timestamp;
            }
            if (isset($validatedData['end_at'])) {
                $updateData['end_at'] = $customerSubDuration->customerSub->subscription_type == 'days_system'
                    ? null
                    : Carbon::parse($validatedData['end_at'])->timestamp;
            }
            if (isset($validatedData['days_left'])) {
                $updateData['days_left'] = $customerSubDuration->customerSub->subscription_type == 'days_system'
                    ? $validatedData['days_left']
                    : null;
            }
            // Update the CustomerSubDuration record
            $customerSubDuration->update($updateData);
        }else{
            return response()->json(['error'=>'Not authorized'],403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerSubDuration $customerSubDuration)
    {
        abort(404);
    }
}
