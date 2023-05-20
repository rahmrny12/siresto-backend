<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RestoController;
use App\Http\Controllers\API\KategoriBisnisController;
use App\Http\Controllers\API\KategoriProdukController;
use App\Http\Controllers\API\LevelController;
use App\Http\Controllers\API\ProdukController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\LaporanController;
use App\Http\Controllers\API\PromoController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\MejaController;
use App\Http\Controllers\API\PembayaranController;
use App\Http\Controllers\API\MenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// resto
Route::apiResource('/resto', RestoController::class)->except('show')->middleware('auth:api');
Route::get('resto/setting-resto', [RestoController::class, 'setting_resto']); // mengambil setting resto
Route::prefix('resto')->group(function () {
    Route::get('/show', [RestoController::class, 'resto_row']);
    Route::get('/all', [RestoController::class, 'resto_all']);
    Route::put('/ubah-status/{resto}', [RestoController::class, 'ubah_status']);
});

// kategori-bisnis
Route::apiResource('/kategori-bisnis', KategoriBisnisController::class)->except('show');
Route::get('kategori-bisnis/all', [KategoriBisnisController::class, 'kategori_bisnis_all']);
Route::prefix('kategori-bisnis')->group(function () {
    Route::post('/insert-select', [KategoriBisnisController::class, 'insert_select']);
});

// kategori-produk
Route::apiResource('/kategori-produk', KategoriProdukController::class)->except('show')->middleware('auth:api');
Route::get('kategori-produk/menu', [KategoriProdukController::class, 'kategori_produk_menu']);
Route::get('kategori-produk/all', [KategoriProdukController::class, 'kategori_produk_all'])->middleware('auth:api');

// level
Route::apiResource('level', LevelController::class)->except('show');

// produk
Route::apiResource('produk', ProdukController::class)->except('show')->middleware('auth:api');
Route::put('produk/ubah-status/{produk}', [ProdukController::class, 'ubah_status'])->middleware('auth:api');
Route::get('produk/produk-home', [ProdukController::class, 'produk_home']);


// meja
Route::apiResource('meja', MejaController::class)->except('show')->middleware('auth:api');
Route::get('meja/no-meja', [MejaController::class, 'no_meja']);
Route::get('meja/all', [MejaController::class, 'meja_all']);

// order
Route::apiResource('order', OrderController::class)->except('show')->middleware('auth:api');
Route::get('order/cari-order-transaksi', [OrderController::class, 'cari_order_by_transaksi'])->middleware('auth:api');
Route::post('order/simpan-order-konsumen', [OrderController::class, 'simpan_order_konsumen'])->middleware('auth:api');

Route::prefix('order')->group(function () {
    Route::patch('/ubah-status-order/{order}', [OrderController::class, 'ubah_status_profile']);
    Route::get('/meja', [OrderController::class, 'meja'])->middleware('auth:api');
});

// laporan
Route::prefix('laporan')->group(function () {
    Route::get('/laporan-penjualan', [LaporanController::class, 'laporan_penjualan'])->middleware('auth:api');
    Route::get('/laporan-pendapatan', [LaporanController::class, 'laporan_pendapatan'])->middleware('auth:api');
});

// promo
Route::get('promo/menu', [PromoController::class, 'promo_menu']);
Route::apiResource('promo', PromoController::class)->middleware('auth:api');

// staff
Route::apiResource('staff', StaffController::class)->middleware('auth:api');
Route::prefix('staff')->group(function () {
    Route::put('/reset-password/{staff}', [StaffController::class, 'reset_password'])->middleware('auth:api');
    Route::put('/ubah-profile/{staff}', [StaffController::class, 'ubah_profile'])->middleware('auth:api');
});

// setting
Route::prefix('setting')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->middleware('auth:api');
    Route::post('/', [SettingController::class, 'store'])->middleware('auth:api');
    Route::put('/profile', [SettingController::class, 'profile'])->middleware('auth:api');
    Route::get('/profile', [SettingController::class, 'get_profile'])->middleware('auth:api');
    Route::get('/profile-user', [SettingController::class, 'profile_user'])->middleware('auth:api');
    Route::patch('/reset-password', [SettingController::class, 'reset_password'])->middleware('auth:api');
    Route::patch('/ubah-profile', [SettingController::class, 'ubah_profile'])->middleware('auth:api');
});

// dashboard
Route::prefix('dashboard')->group(function () {
    Route::get('/owner', [DashboardController::class, 'owner'])->middleware('auth:api');
    Route::get('/superadmin', [DashboardController::class, 'superadmin'])->middleware('auth:api');
});

// frontend
Route::post('pembayaran', [PembayaranController::class, 'store']);
Route::get('menu/cek-pelanggan', [MenuController::class, 'cek_pelanggan']);
Route::post('menu/simpan-order-pelanggan', [MenuController::class, 'simpan_order_pelanggan']);
Route::get('menu/cari-order-transaksi', [MenuController::class, 'cari_order_transaksi']);

// auth admin
Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});
