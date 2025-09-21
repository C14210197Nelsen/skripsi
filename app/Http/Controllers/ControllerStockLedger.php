<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use App\Models\Product;
use Illuminate\Http\Request;

class ControllerStockLedger extends Controller {
    // Tampilkan daftar kartu stok semua produk
    public function index() {
        $products = Product::with(['latestStockLedger'])->get();

        return view('stockledger.index', [
            'products' => $products,
            'title' => 'Stock Movement'
        ]);
    }

    public function show($productID){
        // Ambil produk + histori stock_ledger-nya
        $product = Product::findOrFail($productID);

        $ledgers = $product->stockLedgers()
            ->orderBy('created_at', 'asc')
            ->paginate(10); // Atur jumlah baris per halaman sesuai kebutuhan

        return view('inventory.stockledger', [
            'title' => 'Stock Movement',
            'user' => 'Nama',
            'product' => $product,
            'ledgers' => $ledgers
        ]);

    }
}
