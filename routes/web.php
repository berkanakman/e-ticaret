<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\ProductVariantController;

Route::middleware(['auth:admin','role:superadmin'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('permissions', PermissionController::class)->except(['show']);
});

Route::get('/', function () {
    return view('welcome');
});

// Admin paneli
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    })->name('home');

    // Giriş
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::middleware(['auth:admin'])->group(function () {
        Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Kategoriler
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::post('categories/reorder', [CategoryController::class, 'reorderTopLevel'])
            ->name('categories.reorder')
            ->middleware(['auth:admin','role:superadmin|manager']);
        Route::post('categories/{category}/children/reorder', [CategoryController::class, 'reorderChildren'])
            ->name('categories.children.reorder');

        // Ürünler
        Route::resource('products', ProductController::class);
        Route::resource('attributes', ProductAttributeController::class);
        Route::post('attributes/{attribute}/options/ajax', [ProductAttributeController::class,'ajaxAddOption'])->name('attributes.options.ajax');
        Route::post('attributes/{attribute}/options/reorder', [ProductAttributeController::class,'reorderOptions'])->name('attributes.options.reorder');

        Route::post('products/_slug_check', function (\Illuminate\Http\Request $request) {
            $base = \Illuminate\Support\Str::slug($request->input('base',''));
            $ignore = $request->input('ignore_id');
            $slug = $base;
            $i = 0;
            while (\App\Models\Product::where('slug', $slug)->when($ignore, fn($q) => $q->where('id','!=',$ignore))->exists()) {
                $i++;
                $slug = $base . '_' . $i;
            }
            return response()->json(['slug'=>$slug]);
        });

        // ✅ Yeni eklenen varyant route'ları
        Route::post('products/{product}/variants/manual', [ProductVariantController::class, 'storeManual'])
            ->name('products.variants.manual');
        Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generateMissing'])
            ->name('products.variants.generate');

        // Quill Upload
        Route::post('uploads/quill-image', [UploadController::class, 'quillImage'])
            ->name('uploads.quill-image')
            ->middleware('auth:admin');

        Route::resource('orders', OrdersController::class);

        // Yönetim ekranları (superadmin)
        Route::middleware('role:superadmin')->group(function () {
            Route::resource('admins', AdminController::class);
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::resource('permissions', PermissionController::class)->except(['show']);
        });
    });
});
