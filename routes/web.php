<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdukPublicController;
use App\Http\Controllers\PurchaseController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index']);
Route::get('/barang', [ProdukPublicController::class, 'index'])->name('barang.index');
Route::get('/barang/{product}', [ProdukPublicController::class, 'show'])->name('barang.show');
Route::get('/kontak', fn () => Inertia::render('Kontak'))->name('kontak');
Route::get('/about', fn () => Inertia::render('About'))->name('about');
Route::get('/cart', fn () => Inertia::render('Cart'))->name('cart');
Route::post('/checkout', [PurchaseController::class, 'store'])->name('checkout.store');
Route::get('/admin/hash', function () {
    return Inertia::render('Admin/Hash');
})->name('admin.hash'); // Tidak memakai ->middleware('auth')




Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('dashboard.admin');
    } else {
        return redirect()->route('dashboard.user');
    }
})->name('dashboard');

Route::middleware(['auth'])->group(function () {

    // Admin Area
    Route::prefix('dashboard/admin')->middleware('can:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard.admin');
        Route::resource('produk', ProductController::class);

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/masuk', [InventoryController::class, 'storeMasuk'])->name('inventory.masuk');
        Route::post('inventory/keluar', [InventoryController::class, 'storeKeluar'])->name('inventory.keluar');
        Route::get('inventory/statistik', [InventoryController::class, 'statistik'])->name('inventory.statistik');
        Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
        Route::get('inventory/laporan', [InventoryController::class, 'report'])->name('inventory.laporan');


        // Users kosong :)
        Route::resource('users', UserController::class);
    });

    // User Dashboard kosong :)
    Route::prefix('dashboard/user')->group(function () {
        Route::get('/', fn () => Inertia::render('User/Dashboard'))->name('dashboard.user');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
