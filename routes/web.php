<?php

use App\Http\Controllers\Admin\AdminFles;
use App\Http\Controllers\Admin\AuthLoader as AdminAuthLoader;
use Inertia\Inertia;
use App\Models\AuthLoader;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    // Create Roles and Assign Permissions
    return Application::VERSION;
});

Route::prefix('admins')->middleware( ['auth','verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
    Route::resource('loader-updates', AdminAuthLoader::class);
    Route::resource('files', AdminFles::class);
    Route::post('/files/product', [AdminFles::class, 'createProduct'])->name('files.create.products');
    Route::get('/products', function () {
        return Inertia::render('Products');
    })->middleware(['auth', 'verified'])->name('products');

    Route::get('/orders', function () {

        return Inertia::render('Orders');
    })->middleware(['auth', 'verified'])->name('orders');

    Route::get('/users', function () {
        return Inertia::render('Users');
    })->middleware(['auth', 'verified'])->name('users');
});
Route::get('test',function(){
    return 'test';
});


Route::middleware( 'auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
