<?php

namespace App\Http\Controllers\LoaderLogic;

use Carbon\Carbon;
use App\Models\License;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LicenseLogic extends Controller
{
    /**
     * Start the specified license.
     */
    public function start(Request $request, $id)
    {
        // Retrieve the license and ensure it belongs to the authenticated user
        $license = License::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$license) {
            return response()->json(['error' => 'License not found or unauthorized'], 404);
        }

        // Check if the license has already been started
        if ($license->started_at) {
            return response()->json(['error' => 'License has already been started'], 400);
        }

        // Check if the license is frozen or banned
        if ($license->frozen_at) {
            return response()->json(['error' => 'Cannot start a frozen license'], 400);
        }

        if ($license->banned_at) {
            return response()->json(['error' => 'Cannot start a banned license'], 400);
        }

        // **Set the started_at time**
        $license->started_at = Carbon::now();

        // Calculate the total duration in hours
        $totalDurationInHours = ($license->subscription_duration ?? 0) + ($license->extra_time ?? 0);

        // **Calculate the end_at time**
        $license->end_at = $license->started_at->copy()->addHours($totalDurationInHours);

        // Save the updated license
        $license->save();

        return response()->json(['success' => true, 'data' => $license], 200);
    }
    /**
     * Add extra time to an existing license.
     */
    public function addExtraTime(Request $request, $id)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer|min:0',
            'hours' => 'nullable|integer|min:0',
            'minutes' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails() || !$request->hasAny(['days', 'hours', 'minutes'])) {
            return response()->json(['error' => 'Please provide at least one of days, hours, or minutes, and ensure they are non-negative integers.'], 400);
        }

        // Retrieve the license and ensure it belongs to the authenticated user
        $license = License::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$license) {
            return response()->json(['error' => 'License not found or unauthorized'], 404);
        }

        // Calculate the extra time to add in hours (allowing fractional hours)
        $days = $request->input('days', 0);
        $hours = $request->input('hours', 0);
        $minutes = $request->input('minutes', 0);

        $daysInHours = $days * 24;
        $minutesInHours = $minutes / 60;

        $additionalExtraTime = $daysInHours + $hours + $minutesInHours;

        if ($additionalExtraTime <= 0) {
            return response()->json(['error' => 'Total extra time must be greater than zero'], 400);
        }

        // Add the additional extra time to the license's existing extra_time
        $license->extra_time = ($license->extra_time ?? 0) + $additionalExtraTime;

        // If the license has already started, update the end_at field
        if ($license->started_at) {
            $license->end_at = $license->end_at->copy()->addHours($additionalExtraTime);
        }

        // Save the updated license
        $license->save();

        return response()->json(['success' => true, 'data' => $license], 200);
    }
}
