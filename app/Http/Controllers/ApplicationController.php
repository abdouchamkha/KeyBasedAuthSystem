<?php

namespace App\Http\Controllers;

use App\ActiveType;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Resources\Application as ResourceApplication;
use App\Http\Requests\Application\UpdateApplicationRequest;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get the allowed apps to viewe
        $customer = User::whereId(auth()->id())->with(['applications', 'customerOf'])->first();
        $appIds = $customer->applications->pluck('id')->toArray();  // Get the IDs from applications
        $customerOfIds = $customer->customerOf->pluck('app_id')->toArray();  // Get the IDs from customerOf
        $combinedIds = array_merge($appIds, $customerOfIds);
        return ResourceApplication::collection(Application::whereIn('id', $combinedIds)->paginate());
        // return $applications;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApplicationRequest $request)
    {
        $application = Application::create([
            'name' => $request->validated()['name'],
            'owner_id' => auth()->id(),
            'status' => ActiveType::ACTIVE,
        ]);

        return response()->json($application, 201); // Return the created application with a 201 status
    }
    /**
     * Reset the token for the given application.
     */
    public function resetToken($id)
    {
        $application = Application::find($id)->where('owner_id',auth()->id)->first();
        if(!$application){
            return response()->json(['error'=>'Application not found or not autorized'],405);
        }

        // Reset token and get new plain token
        $newToken = $application->resetToken();

        return response()->json([
            'success' => true,
            'message' => 'Token reset successfully.',
            'token' => $newToken, // Only show the plain token once
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        return new ResourceApplication($application);
        // abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApplicationRequest $request, Application $app)
    {
        return response()->json( $app->update($request->validated()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application)
    {
        if ($application->owner_id === auth()->id()) {
            $application->delete();
            return response()->json(['message' => 'Application deleted successfully!'],201);
        }else{
            return response()->json(['error'=>'Unauthorized to delete this application'],401);
        }
    }
}
