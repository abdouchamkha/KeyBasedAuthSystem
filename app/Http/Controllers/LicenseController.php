<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\License\StoreLicenseRequest;
use App\Http\Resources\License as ResourceLicense;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = License::with([ 'product'])->where('user_id', auth()->id());

        // Apply search filter if provided
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('license_value', 'like', "%{$search}%")
                  ->orWhere('uuid_value', 'like', "%{$search}%");
            });
        }

        // Apply filters if provided
        $frozen = $request->input('frozen');
        if ($frozen) {
            $query->whereNotNull('frozen_at');
        }

        $started = $request->input('started');
        if ($started) {
            $query->whereNotNull('started_at');
        }

        $product = $request->input('product');
        if ($product && is_numeric($product)) {
            $query->where('product_id', $product);
        }

        $banned = $request->input('banned');
        if ($banned) {
            $query->whereNotNull('banned_at');
        }

        $extra_time = $request->input('has_extra_time');
        if ($extra_time) {
            $query->whereNotNull('extra_time');
        }

        $hwid_lock = $request->input('hwid_lock');
        if ($hwid_lock) {
            $query->where('hwid_lock', true);
        }

        $paginate = is_numeric($request->input('paginate')) ? $request->input('paginate') : 10;

        return ResourceLicense::collection($query->paginate($paginate));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenseRequest $request)
    {
        $validatedData = $request->validated();

        $product = Product::where('id', $validatedData['product'])
            ->where('user_id', auth()->id())
            ->first();

        if (auth()->user()->selectedApp()->first()->id != $validatedData['app'] || !$product) {
            return response()->json(['error' => 'Unauthorized to store this license'], 401);
        }

        // Calculate the total subscription duration in hours
        $subscriptionDuration = ($validatedData['days'] * 24) + $validatedData['hours'] + ceil($validatedData['minutes'] / 60);
        if ($subscriptionDuration == 0) {
            return response()->json(['error' => 'The license need to be more then 0 hours.'], 400);
        }
        // Generate a unique license key (customize the format as needed)
        // $licenseKey = strtoupper(Str::random(16));

        $license = License::create([
            'app_id'                => $validatedData['app'],
            'product_id'            => $product->id,
            'user_id'               => auth()->id(),
            'license_value'         => Str::uuid(),
            'uuid_value'            => Str::uuid(),
            'hwid_lock'             => $validatedData['hwid_lock'],
            'subscription_duration' => $subscriptionDuration,
            // Other fields are nullable and can be omitted if not set
        ]);

        return response()->json(['success' => true, 'data' => $license], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(License $license)
    {
        // Ensure the license belongs to the authenticated user
        if ($license->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized access to this license'], 403);
        }

        // Eager load relationships
        $license->load([ 'sessions', 'hwid']);

        // Return the resource
        return new ResourceLicense($license);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, License $license)
    {
        // Implement update logic if needed
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
    {
        // Implement destroy logic if needed
    }
    // Example of generating a license key with segments
    public function generateLicenseKey($segments = 4, $segmentLength = 4)
    {
        $key = '';
        for ($i = 0; $i < $segments; $i++) {
            $key .= strtoupper(Str::random($segmentLength));
            if ($i < ($segments - 1)) {
                $key .= '-';
            }
        }
        return $key;
    }
}
