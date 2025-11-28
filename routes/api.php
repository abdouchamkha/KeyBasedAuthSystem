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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerSubDurationController;
use App\Http\Controllers\LoaderLogic\Index as IndexClass;
use App\Http\Controllers\ProductDownloadController;
use App\Models\AuthLoader;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use App\Http\Controllers\LoaderLogic\UiLoader;

// Public Routes
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
Route::get('test',function(){
    return 'test';
});

Route::get('/auth', fn() => ['data' => \Illuminate\Support\Facades\Auth::check()])->name('auth.check');

Route::get('/noui-version', fn() =>
['versions'=>$auth_loader = AuthLoader::select(['id', 'version', 'created_at', 'unsupported_at'])->latest()->limit(2)->get()])->name('auth.version');

// Reset Password
Route::post('/reset-password', [NewPasswordController::class, 'storeApi'])
    ->name('password.reset');

// Protected Routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    // Application Routes
    Route::apiResource('app', ApplicationController::class)->except('show');
    Route::post('/app/{id}/reset-token', [ApplicationController::class, 'resetToken']);
    Route::get('app/{application}', [ApplicationController::class, 'show']);

    // Customer Routes
    Route::apiResource('customer', CustomerController::class);
    Route::apiResource('customer/sub', CustomerSubController::class);
    Route::apiResource('customer/sub/duration', CustomerSubDurationController::class);

    // Product Routes
    Route::apiResource('product', ProductController::class);
    Route::get('/products', [ProductController::class, 'getProducts'])->name('products.getProducts');

    // File Download Routes
    Route::apiResource('file', ProductDownloadController::class);

    // New Chunked Upload Endpoint
    Route::post('/file/chunk', [ProductDownloadController::class, 'uploadChunk'])->name('file.uploadChunk');

    Route::get('/file-download/{productDownload}', [ProductDownloadController::class, 'download'])->name('file.download');
    // License Routes
    Route::apiResource('license', LicenseController::class);
    Route::get('start/{id}', [LicenseLogic::class, 'start']);
});

// User Profile Routes
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

// Loader Routes
Route::prefix('loader')->group(function () {
    Route::post('/', [IndexClass::class, 'index']);
    Route::get('/license/{license}', [UiLoader::class, 'getLicense']);
    Route::post('/license', [UiLoader::class, 'index']);
    Route::get('/download/noui', [UiLoader::class, 'download']);
});
