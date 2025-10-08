<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ControllerCustomer,
    ControllerProduct,
    ControllerPurchase,
    ControllerReturn,
    ControllerSales,
    ControllerSpending,
    ControllerStockLedger,
    ControllerSupplier,
    ControllerUserlogin,
    ControllerLaporan,
    ControllerRekapan,
    ControllerHome,
    ControllerLogin,
    ControllerAjax,
    ControllerSalesImport,
    ControllerForecast
};

// Login & Logout
Route::middleware('guest')->group(function () {
    Route::get('/login', [ControllerLogin::class, 'showLogin'])->name('login');
    Route::post('/login', [ControllerLogin::class, 'login']);
});

Route::post('/logout', [ControllerLogin::class, 'logout'])->name('logout');

// Authentichation User
Route::middleware('auth')->group(function () {
    Route::get('/home', [ControllerHome::class, 'index'])->name('home');
    Route::get('/', [ControllerHome::class, 'index']);
    Route::get('/get-product/{productCode}', [ControllerAjax::class, 'getProductInfo']);
});


Route::middleware(['auth', 'role:Owner,Purchase,Sales'])->group(function () {

    // -------------------- OWNER --------------------
    Route::middleware('role:Owner')->group(function () {

        // User Management
        Route::resource('/user', ControllerUserlogin::class)->parameters(['user' => 'userlogin']);

        // Spending, Laporan, Rekapan
        Route::resources([
            '/spending' => ControllerSpending::class,
            '/laporan'  => ControllerLaporan::class,
            '/rekapan'  => ControllerRekapan::class,
        ]);

            // -------------------- FORECAST --------------------
        Route::middleware('role:Owner')->prefix('forecast')->group(function () {
            Route::post('/run_forecast/{productID}', [ControllerForecast::class, 'runForecast']);
            Route::post('/train_model/{productID}', [ControllerForecast::class, 'requestForecast'])->name('forecast.request');
        });

    });
    

    // -------------------- PURCHASE --------------------
    Route::middleware('role:Owner,Purchase')->group(function () {

        // Purchase
        Route::resource('/purchase', ControllerPurchase::class);

        // Supplier
        Route::get('/supplier/deleted', [ControllerSupplier::class, 'deleted'])->name('supplier.deleted');
        Route::resource('/supplier', ControllerSupplier::class);
        
        // Inventory
        Route::get('/inventory/shortage', [ControllerProduct::class, 'shortage'])->name('inventory.shortage');
        Route::get('/inventory/deleted', [ControllerProduct::class, 'deleted'])->name('inventory.deleted');
        Route::resource('/inventory', ControllerProduct::class)->parameters(['inventory' => 'product']);
        Route::get('/inventory/{productID}/stockledger', [ControllerStockLedger::class, 'show'])->name('stockledger.show');


    });

    // -------------------- SALES --------------------
    Route::middleware('role:Owner,Sales')->group(function () {

        // Sales
        Route::get('/sales/{id}/print-invoice', [ControllerSales::class, 'printInvoice'])->name('sales.printInvoice');
        Route::post('/sales/import/submit', [ControllerSalesImport::class, 'submitShopee'])->name('sales.import.submit');
        Route::resource('/sales', ControllerSales::class);

        // Customer
        Route::get('/customer/deleted', [ControllerCustomer::class, 'deleted'])->name('customer.deleted');
        Route::resource('/customer', ControllerCustomer::class);




    });

    // -------------------- FORECAST --------------------
    Route::middleware('role:Owner,Sales,Purchase')->prefix('forecast')->group(function () {
        Route::get('/{productID}', [ControllerForecast::class, 'getForecast']);
        Route::get('/status/{jobId}', [ControllerForecast::class, 'checkStatus'])->name('forecast.status');
    });
    Route::resource('/forecast', ControllerForecast::class);

    // Return
    Route::get('/return/form/{type}/{id}', [ControllerReturn::class, 'createFormFromSource'])->name('return.form');
    Route::resource('/return', ControllerReturn::class);

    // Inventory limited: index only
    Route::get('/inventory', [ControllerProduct::class, 'index'])->name('inventory.index');
});
