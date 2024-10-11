<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CustomerSubController;
use App\Http\Controllers\LoaderLogic\LicenseLogic;
use App\Http\Controllers\profile\profileController;
use App\Http\Controllers\CustomerSubDurationController;
use App\Http\Controllers\LoaderLogic\index;
use App\Http\Controllers\LoaderLogic\uiLoader;
use App\Models\AuthLoader;
use Laravel\Fortify\Http\Controllers\NewPasswordController;

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'token_name' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    return $user->createToken($request->token_name)->plainTextToken;
});
Route::get('/auth', fn() => ['data' => \Illuminate\Support\Facades\Auth::check()])->name('auth.check');
Route::get('/noui-version', fn() =>
['versions'=>$auth_loader = AuthLoader::select(['id', 'version', 'created_at', 'unsupported_at'])->latest()->limit(2)->get()])->name('auth.version');

// Reset Password
Route::post('/reset-password', [NewPasswordController::class, 'storeApi'])
    ->name('password.reset');

// Route::post('/reset-password', function (Request $request) {
//     $request->validate([
//         'token' => 'required',
//         'email' => 'required|email',
//         'password' => 'required|min:8|confirmed',
//     ]);

//     $status = Password::reset(
//         $request->only('email', 'password', 'password_confirmation', 'token'),
//         function (User $user, string $password) {
//             $user->forceFill([
//                 'password' => Hash::make($password)
//             ])->setRememberToken(Str::random(60));

//             $user->save();

//             event(new PasswordReset($user));
//         }
//     );

//     return $status === Password::PASSWORD_RESET
//         ? response()->json(['message' => 'Password reset successfully.'])
//         : response()->json(['email' => [__($status)]], 422);
// })->name('password.reset'); // Make sure this route name is defined
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::apiResource('app', ApplicationController::class)->except('show');
    Route::post('/app/{id}/reset-token', [ApplicationController::class, 'resetToken']);
    Route::get('app/{application}', [ApplicationController::class, 'show']);
    Route::apiResource('customer', CustomerController::class);
    Route::apiResource('customer/sub', CustomerSubController::class);
    Route::apiResource('customer/sub/duration', CustomerSubDurationController::class);
    Route::apiResource('product', ProductController::class);
    Route::get('/products', [ProductController::class, 'getProducts'])->name('products.getProducts');
    Route::apiResource('license', LicenseController::class);
    Route::get('start/{id}',[ LicenseLogic::class,'start']);
});
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'user'], function () {
    Route::get('/', function () {
        $user = User::with('applications','customerOf','selectedApp')->whereId(auth()->id())->firstOrFail();
        return response()->json($user);
    });

    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::post('/update-password', 'changePassword');
        Route::post('/update-information', 'update');
    });
});
Route::prefix( 'loader')->group(function () {
    Route::post('/', [index::class,'index']);
    Route::get('/license/{license}', [uiLoader::class,'getLicense']);
    Route::post('/license', [uiLoader::class,'init']);
});
