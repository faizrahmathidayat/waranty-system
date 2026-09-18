<?php


use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ServiceCompletionController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\Cms\ArticleController;
use App\Http\Controllers\Cms\CatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', function () {
    return view('login/index');
});
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


//login
Route::post('/login/auth', [LoginController::class, 'auth'])->name('login.auth');
Route::get('/login/logout', [LoginController::class, 'logout'])->name('login.logout');

//customer
route::get('/customer', [CustomerController::class, 'index']);
route::get('/customer/json', [CustomerController::class, 'data']);
Route::post('/customer/store', [CustomerController::class, 'store']);
Route::get('/customer/show/{id_customer}', [CustomerController::class, 'show']);
Route::post('/customer/update', [CustomerController::class, 'update']);
Route::post('/customer/destroy', [CustomerController::class, 'destroy']);

// Product
Route::get('/product', [ProductController::class, 'index']);
Route::get('/product/json', [ProductController::class, 'data']);
Route::post('/product/store', [ProductController::class, 'store']);
Route::get('/product/show/{id_product}', [ProductController::class, 'show']);
Route::post('/product/update', [ProductController::class, 'update']);
Route::post('/product/destroy', [ProductController::class, 'destroy']);

foreach (['product-type' => ProductTypeController::class, 'treatment' => TreatmentController::class, 'product-variant' => ProductVariantController::class, 'vehicle' => VehicleController::class, 'building' => BuildingController::class, 'technician' => TechnicianController::class] as $prefix => $controller) {
    Route::get("/{$prefix}", [$controller, 'index']);
    Route::get("/{$prefix}/json", [$controller, 'data']);
    Route::post("/{$prefix}/store", [$controller, 'store']);
    Route::get("/{$prefix}/show/{id}", [$controller, 'show']);
    Route::post("/{$prefix}/update", [$controller, 'update']);
    Route::post("/{$prefix}/destroy", [$controller, 'destroy']);
}

// Order Automotive (invoice, payment, and warranty generation are intentionally separate phases)
Route::get('/order', [OrderController::class, 'index']);
Route::get('/order/json', [OrderController::class, 'data']);
Route::get('/order/create', [OrderController::class, 'create']);
Route::post('/order/store', [OrderController::class, 'store']);
Route::get('/order/show/{id}', [OrderController::class, 'show']);
Route::get('/order/edit/{id}', [OrderController::class, 'edit']);
Route::post('/order/update/{id}', [OrderController::class, 'update']);
Route::post('/order/confirm/{id}', [OrderController::class, 'confirm']);
Route::post('/order/cancel/{id}', [OrderController::class, 'cancel']);
Route::get('/order/customer/{id_customer}/vehicles', [OrderController::class, 'customerVehicles']);
Route::get('/order/customer/{id_customer}/buildings', [OrderController::class, 'customerBuildings']);
Route::get('/order/treatment/{id_treatment}/products', [OrderController::class, 'treatmentProducts']);
Route::get('/order/product/{id_product}/variants', [OrderController::class, 'productVariants']);
Route::post('/order/{id}/generate-warranty', [ServiceCompletionController::class, 'generate']);

Route::get('/invoice', [InvoiceController::class, 'index']);
Route::get('/invoice/json', [InvoiceController::class, 'data']);
Route::post('/invoice/generate/{id_order}', [InvoiceController::class, 'generate']);
Route::get('/invoice/show/{id}', [InvoiceController::class, 'show']);
Route::post('/invoice/cancel/{id}', [InvoiceController::class, 'cancel']);
Route::get('/invoice/print/{id}', [InvoiceController::class, 'print']);
Route::post('/invoice/{id}/payment', [PaymentController::class, 'store']);
Route::post('/invoice/payment/{id}/void', [PaymentController::class, 'void']);
Route::get('/invoice/{id}/payments', [PaymentController::class, 'history']);

// Warranty
Route::get('/warranty', [WarrantyController::class, 'index']);
Route::get('/warranty/json', [WarrantyController::class, 'data']);
Route::post('/warranty/store', [WarrantyController::class, 'store']);
Route::get('/warranty/show/{id_warranty}', [WarrantyController::class, 'show']);
Route::post('/warranty/update', [WarrantyController::class, 'update']);
Route::post('/warranty/destroy', [WarrantyController::class, 'destroy']);
Route::get('/warranty/{kode}', [WarrantyController::class, 'digitalWarranty'])->name('warranty.digital');
Route::get('/warranty/{kode}/check', [WarrantyController::class, 'checkStatus'])->name('warranty.check');
Route::post('/warranty/{kode}/verify-pin', [WarrantyController::class, 'verifyDigitalPin'])->name('warranty.verify-pin');
// A GET here means someone opened the verify-pin URL directly (refresh, back button,
// shared link) instead of submitting the form — send them back to the PIN page.
Route::get('/warranty/{kode}/verify-pin', function (string $kode) {
    return redirect()->route('warranty.digital', $kode);
})->name('warranty.verify-pin');
Route::post('/warranty/void', [WarrantyController::class, 'void'])->name('warranty.void');
//download warranty pdf
Route::get('/warranty/{kode}/pdf', [WarrantyController::class, 'downloadPdf'])->name('warranty.pdf');
Route::get('/warranty/{kode}/print', [WarrantyController::class, 'print'])->name('warranty.print');

//
Route::prefix('user')->group(function () {

    Route::get('/', [UserController::class, 'index'])->name('user.index');
    Route::get('/json', [UserController::class, 'json'])->name('user.json');

    Route::post('/store', [UserController::class, 'store'])->name('user.store');

    Route::get('/show/{id}', [UserController::class, 'show'])->name('user.show');

    Route::post('/update', [UserController::class, 'update'])->name('user.update');

    Route::post('/destroy', [UserController::class, 'destroy'])->name('user.destroy');
});

//dashobard
Route::get('/dashboard/chart/warranty', [DashboardController::class, 'chartWarranty'])->name('dashboard.chart.warranty');
Route::get('/dashboard/chart/status', [DashboardController::class, 'chartStatusWarranty'])->name('dashboard.chart.status');
Route::get('/dashboard/chart/latest-warranty', [DashboardController::class, 'latestWarranty'])->name('dashboard.latest.warranty');
Route::get('/dashboard/warranty-expired-soon', [DashboardController::class, 'warrantyExpiredSoon'])->name('dashboard.warranty.expired.soon');
Route::get('/dashboard/product-terlaris', [DashboardController::class, 'productTerlaris'])->name('dashboard.product.terlaris');

//laproan
Route::get('/laporan-mobil', [LaporanController::class, 'mobil'])->name('laporan.mobil');
Route::get('/laporan-mobil/data', [LaporanController::class, 'mobilData'])->name('laporan.mobil.data');
Route::get('/laporan-mobil/export-excel', [LaporanController::class, 'exportExcel'])->name('laporan.mobil.export.excel');
Route::get('/laporan-mobil/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.mobil.export.pdf');
Route::get('/laporan-building', [LaporanController::class, 'building'])->name('laporan.building');
Route::get('/laporan-building/data', [LaporanController::class, 'buildingData'])->name('laporan.building.data');
Route::get('/laporan-building/export-excel', [LaporanController::class, 'buildingExportExcel'])->name('laporan.building.export.excel');
Route::get('/laporan-building/export-pdf', [LaporanController::class, 'buildingExportPdf'])->name('laporan.building.export.pdf');
Route::get('/laporan-invoice', [LaporanController::class, 'invoice'])->name('laporan.invoice');
Route::get('/laporan-invoice/data', [LaporanController::class, 'invoiceData'])->name('laporan.invoice.data');
Route::get('/laporan-invoice/export-excel', [LaporanController::class, 'invoiceExportExcel'])->name('laporan.invoice.export.excel');
Route::get('/laporan-invoice/export-pdf', [LaporanController::class, 'invoiceExportPdf'])->name('laporan.invoice.export.pdf');

// CMS - Artikel
Route::prefix('cms/articles')->name('cms.articles.')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/data', [ArticleController::class, 'data'])->name('data');
    Route::get('/create', [ArticleController::class, 'index'])->name('create');
    Route::get('/{article}/edit', [ArticleController::class, 'index'])->name('edit');
    Route::get('/{article}', [ArticleController::class, 'show'])->name('show');
    Route::post('/', [ArticleController::class, 'store'])->name('store');
    Route::put('/{article}', [ArticleController::class, 'update'])->name('update');
    Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
    Route::delete('/media/{media}', [ArticleController::class, 'destroyMedia'])->name('media.destroy');
});

// CMS - Katalog
Route::prefix('cms/catalog')->name('cms.catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('/data', [CatalogController::class, 'data'])->name('data');
    Route::get('/create', [CatalogController::class, 'index'])->name('create');
    Route::get('/{catalogItem}/edit', [CatalogController::class, 'index'])->name('edit');
    Route::get('/{catalogItem}', [CatalogController::class, 'show'])->name('show');
    Route::post('/', [CatalogController::class, 'store'])->name('store');
    Route::put('/{catalogItem}', [CatalogController::class, 'update'])->name('update');
    Route::delete('/{catalogItem}', [CatalogController::class, 'destroy'])->name('destroy');
    Route::delete('/media/{media}', [CatalogController::class, 'destroyMedia'])->name('media.destroy');
});
