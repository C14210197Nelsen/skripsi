<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Http\Request;

class ControllerProduct extends Controller {
    public function index() {
        $products = Product::where('status', 1)
        ->orderBy('productID', 'desc')
        ->get();
        return view('inventory.index', [
            'title' => 'Inventory',
            'user' => 'Nama',
            'products' => $products
        ]);
    }

    public function deleted() {
        $deletedProducts = Product::where('status', 0)
        ->orderBy('productID', 'desc')
        ->get();
        return view('inventory.deleted', [
            'title' => 'Deleted Products',
            'user' => 'Nama Pengguna',
            'products' => $deletedProducts
        ]);
    }

    public function create() {
        return view('inventory.create', [
            'title' => 'Create Product',
            'user' => 'Nama'
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'productCode' => 'required|string|max:16|unique:product,productCode',
            'productName' => 'required|string|max:255',
            'productPrice' => 'required|integer|max:9999999999',
            'productCost' => 'required|integer|max:9999999999',
            'productType' => 'nullable|string|max:8',
            'stock' => 'nullable|integer',
            'minStock' => 'nullable|integer',
            'status' => 'required|boolean'
        ]);

        $validated['stock'] = $request->filled('stock') ? (int) $request->stock : 0;
        $validated['minStock'] = $request->filled('minStock') ? (int) $request->minStock : 0;

        $product = Product::create($validated);

        // Stock_ledger pertama jika ada stok awal
        if ($validated['stock'] >= 0) {
            StockLedger::create([
                'productID' => $product->productID,
                'qty' => $validated['stock'],
                'saldo_qty' => $validated['stock'],
                'saldo_harga' => $validated['stock'] * $validated['productCost'],
                'price' => $validated['productCost'],
                'total_price' => $validated['stock'] * $validated['productCost'],
                'hpp' => $validated['productCost'],
                'type' => 'Initial',
                'source_type' => 'CREATE',
                'source_id' => $product->productID,
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Produk berhasil ditambahkan!');
    }


    public function edit(Product $product) {
        return view('inventory.edit', [
            'title' => 'Edit Product',
            'user' => 'Nama',
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product) {
        $validated = $request->validate([
            'productCode' => 'required|string|max:16|unique:product,productCode,' . $product->productID . ',productID',
            'productName' => 'required|string|max:255',
            'productPrice' => 'required|integer',
            'productCost' => 'integer',
            'productType' => 'nullable|string|max:8',
            'stock' => 'integer',
            'minStock' => 'nullable|integer',
            'status' => 'required|boolean'
        ]);

        $last = StockLedger::where('productID', $product->productID)->latest('created_at')->first();

        $oldStock = $last ? (int) $last->saldo_qty : 0;
        $oldCost  = $last ? (int) $last->price     : 0;


        $newStock = (int) $request->input('stock', $oldStock);
        $newCost  = (int) $request->input('productCost', $oldCost);

        $stockChanged = $newStock != $oldStock;
        $costChanged  = $newCost  != $oldCost;


        // Jika perlu, buat ledger baru
        if ($stockChanged || $costChanged) {
            StockLedger::create([
                'productID'     => $product->productID,
                'qty'           => $newStock - $last->saldo_qty ?? 0,
                'saldo_qty'     => $newStock,
                'saldo_harga'   => $newStock * $newCost,
                'price'         => $newCost,
                'total_price'   => $newStock * $newCost,
                'hpp'           => $newCost,
                'type'          => 'Adjust',
                'source_type'   => 'UPDATE',
                'source_id'     => $product->productID,
            ]);
        }

        $product->update([
            'productCode'   => $request->productCode,
            'productName'   => $request->productName,
            'productPrice'  => $request->productPrice,
            'productCost'   => $newCost,
            'productType'   => $request->productType,
            'stock'         => $newStock,
            'minStock'      => $request->has('minStock') ? (int) $request->minStock : $product->minStock,
            'status'        => $request->status,
        ]);
        

        return redirect()->route('inventory.index')->with('success', 'Produk berhasil diperbarui!');
    }

    public function destroy(Product $product) {
        $product->update(['status' => 0]);

        return redirect()->route('inventory.index')->with('success', 'Produk berhasil dinonaktifkan.');
    }


}
