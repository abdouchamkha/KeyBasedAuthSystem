<?php

namespace App\Http\Controllers\profile;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Actions\Fortify\UpdateUserPassword;

class profileController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->update($validated);

        return response()->json(['success' => true]);
    }
    public function changePassword(Request $request, UpdateUserPassword $updater)
    {
        $updater->update(
            auth()->user(),
            [
                'current_password' => $request->current_password,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ]
        );

        return response()->json(['message' => 'Password changed successfully!']);
    }
}
