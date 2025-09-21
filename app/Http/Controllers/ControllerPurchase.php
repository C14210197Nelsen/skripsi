<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Returnorder;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use App\Models\Purchaseorder;
use App\Models\Purchasedetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class ControllerPurchase extends Controller {

    public function index(Request $request) {
        // Validasi bulan from <= to
        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $to = \Carbon\Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();

            if ($from > $to) {
                return back()->withErrors(['from' => 'Bulan 1 tidak boleh setelah bulan 2.'])->withInput();
            }
        }

        // Query
        $query = Purchaseorder::with('supplier')
            ->where('status', 1)
            // ->orderBy('PurchaseDate', 'desc')
            ->orderBy('PurchaseID', 'desc');

        if ($request->filled('supplier_id')) {
            $query->where('Supplier_supplierID', $request->supplier_id);
        }

        if ($request->filled('from')) {
            $from = \Carbon\Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $query->whereDate('purchaseDate', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = \Carbon\Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();
            $query->whereDate('purchaseDate', '<=', $to);
        }

        // Pagination
        $purchaseorders = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::where('status', 1)->get();

        return view('purchase.index', [
            'title' => 'Purchase',
            'user' => 'Nama',
            'purchaseorders' => $purchaseorders,
            'suppliers' => $suppliers,
        ]);
    }

    public function create() {
        $suppliers = Supplier::where('status', 1)->get();
        $products = Product::where('status', 1)->orderBy('productID', 'desc')->get(); 
        return view('purchase.create', [
            'title' => 'Purchase',
            'user' => 'Nama',
            'suppliers' => $suppliers,
            'products' => $products,
        ]);

    }

    public function store(Request $request) {

        $request->validate([
            'supplier_id' => 'required|exists:supplier,supplierID',
            'purchaseDate' => 'required|date',
            'products' => 'required|array',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.cost' => 'required|numeric|min:0',
        ]);

        // Cek Produk Code
        foreach ($request->products as $item) { 
            $product = Product::where('productCode', $item['productCode'])->first();

            if (!$product->status) {
                return back()->withErrors([
                    'stok' => "Produk {$product->productCode} tidak aktif dan tidak dapat dibeli."
                ])->withInput();
            }
        }

        // Simpan header PO
        $purchaseOrder = Purchaseorder::create([
            'purchaseDate' => $request->purchaseDate,
            'Supplier_supplierID' => $request->supplier_id,
            'status' => 1,
            'totalPrice' => 0,
        ]);

        $totalPrice = 0;

        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
          
            
            $qty = $item['quantity'];
            $price = $item['cost'];
            $subtotal = $qty * $price;

            // Simpan detail pembelian
            Purchasedetail::create([
                'PurchaseOrder_purchaseID' => $purchaseOrder->purchaseID,
                'Product_productID' => $product->productID,
                'quantity' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'status' => 1,
            ]);

            // Ambil saldo terakhir dari ledger
            $last = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();
            $last_qty = $last->saldo_qty ?? 0;
            $last_cost = $last->saldo_harga ?? 0;

            $new_saldo_qty = $last_qty + $qty;
            $new_saldo_harga = $last_cost + ($qty * $price);

            // Catat ledger in
            StockLedger::create([
                'productID' => $product->productID,
                'qty' => $qty,
                'saldo_qty' => $new_saldo_qty,
                'saldo_harga' => $new_saldo_harga,
                'price' => $price,
                'total_price' => $price * $qty,
                'hpp' => $price,
                'type' => 'Purchase-In',
                'source_type' => 'PO',
                'source_id' => $purchaseOrder->purchaseID,
            ]);

            $totalPrice += $subtotal;
        }

        // Update total header
        $purchaseOrder->update([
            'totalPrice' => $totalPrice,
        ]);

        return redirect()->route('purchase.create')->with('success', 'Transaksi berhasil disimpan.');
    }


    public function show($id) {
        $purchaseorder = Purchaseorder::with(['supplier', 'purchasedetails.product'])->findOrFail($id);

        return view('purchase.show', [
            'title' => 'Purchase Order Detail',
            'user' => 'Nama',
            'purchaseorder' => $purchaseorder
        ]);
    }


    public function edit($id) {
        $purchaseorder = Purchaseorder::with(['purchasedetails.product'])->findOrFail($id);
        
        $suppliers = Supplier::where('status', 1)
            ->orWhere('supplierID', $purchaseorder->Supplier_supplierID)
            ->get();
        $products = Product::where('status', 1)->orderBy('productID', 'desc')->get();

        return view('purchase.edit', [
            'title' => 'Purchase Order Detail',
            'user' => 'Nama',
            'purchaseorder' => $purchaseorder,
            'suppliers' => $suppliers,
            'products' => $products
        ]);

    }
    
    public function update(Request $request, $id) {
        $request->validate([
            'supplier_id' => 'required|exists:supplier,supplierID',
            'purchaseDate' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.cost' => 'required|numeric|min:0',
        ]);

        // Validasi produk nonaktif
        $purchaseOrder = Purchaseorder::with('purchasedetails')->findOrFail($id);
        $oldDetails = $purchaseOrder->purchasedetails->keyBy('Product_productID');

        $nonactive = [];

        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
            $productID = $product->productID;

            $isNewProduct = !isset($oldDetails[$productID]); // produk baru ditambahkan ke PO
            $oldQty = $oldDetails[$productID]->quantity ?? 0;
            $newQty = $item['quantity'];

            if ($isNewProduct && $product->status == 0) {
                // produk baru tapi nonaktif → block
                $nonactive[] = $product->productName;
            }

            if (!$isNewProduct && $product->status == 0 && $newQty > $oldQty) {
                // produk lama tapi nonaktif, qty bertambah → block
                $nonactive[] = $product->productName . " (qty tidak boleh bertambah)";
            }
        }

        if (!empty($nonactive)) {
            return back()
                ->withErrors([
                    'products' => "Produk berikut tidak valid untuk PO: " . implode(', ', $nonactive)
                ])
                ->withInput();
        }


        DB::transaction(function () use ($request, $id) {
            $purchaseOrder = Purchaseorder::with('purchasedetails')->findOrFail($id);
            $oldDetails = $purchaseOrder->purchasedetails->keyBy('Product_productID');
            $purchaseOrder->purchasedetails()->delete();

            $totalPrice = 0;
            $updatedProductIDs = [];

            // Ambil saldo awal semua produk sekali saja
            $initialLedger = [];
            foreach ($request->products as $item) {
                $product = Product::where('productCode', $item['productCode'])->first();
                $last = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();
                $initialLedger[$product->productID] = [
                    'qty' => $last->saldo_qty ?? 0,
                    'harga' => $last->saldo_harga ?? 0,
                ];
            }


            foreach ($request->products as $item) {
                $product = Product::where('productCode', $item['productCode'])->firstOrFail();

                $qty = $item['quantity'];
                $price = $item['cost'];
                $productID = $product->productID;
                $updatedProductIDs[] = $productID;

                $oldQty = $oldDetails[$productID]->quantity ?? 0;
                $returnedQty = $oldDetails[$productID]->returned ?? 0;
                $oldQty -= $returnedQty;

                $oldPrice = $oldDetails[$productID]->price ?? 0;
                $subtotal = $qty * $price;

                // Simpan detail ulang
                Purchasedetail::create([
                    'PurchaseOrder_purchaseID' => $purchaseOrder->purchaseID,
                    'Product_productID' => $productID,
                    'quantity' => $qty + $returnedQty,
                    'returned' => $returnedQty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'status' => 1,
                ]);

                $initial_qty = $initialLedger[$productID]['qty'];
                $initial_harga = $initialLedger[$productID]['harga'];

                if ($oldQty == 0) {
                    // 1. Produk baru
                    $new_qty = $initial_qty + $qty;
                    $new_harga = $initial_harga + ($price * $qty);

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => $qty,
                        'saldo_qty' => $new_qty,
                        'saldo_harga' => $new_harga,
                        'price' => $price,
                        'total_price' => $price * $qty,
                        'hpp' => $price,
                        'type' => 'Purchase-In',
                        'source_type' => 'PO',
                        'source_id' => $purchaseOrder->purchaseID,
                    ]);
                } elseif ($price != $oldPrice) { //20k != 30k
                    // 2 & 3. Harga berubah atau harga + qty berubah → cancel all + masuk all
                    $cancel_qty = -$oldQty; // -5
                    $saldo_qty_cancel = $initial_qty + $cancel_qty; //10 - 5 = 5
                    $saldo_harga_cancel = $initial_harga - ($oldPrice * $oldQty); // 300 - (30k * 5) = 150k

                    // if ($qty == $oldQty) {
                    //     $saldo_qty_cancel = $initial_qty; //10
                    // }
                    // StockLedger::create([
                    //     'productID' => $productID,
                    //     'qty' => $cancel_qty,
                    //     'saldo_qty' => $saldo_qty_cancel,
                    //     'saldo_harga' => $saldo_harga_cancel,
                    //     'price' => $oldPrice,
                    //     'total_price' => $oldPrice * $oldQty,
                    //     'hpp' => $oldPrice,
                    //     'type' => 'Purchase-X',
                    //     'source_type' => 'PO',
                    //     'source_id' => $purchaseOrder->purchaseID,
                    // ]);

                    $saldo_qty_new = $saldo_qty_cancel + $qty;
                    $saldo_harga_new = $saldo_harga_cancel + ($price * $qty);

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => $qty,
                        'saldo_qty' => $saldo_qty_new,
                        'saldo_harga' => $saldo_harga_new,
                        'price' => $price,
                        'total_price' => $price * $qty,
                        'hpp' => $price,
                        'type' => 'Purchase-In',
                        'source_type' => 'PO',
                        'source_id' => $purchaseOrder->purchaseID,
                    ]);
                } elseif ($qty != $oldQty) {
                    // 4. Qty berubah saja → adjust selisih
                    $diff = $qty - $oldQty;

                    if ($diff > 0) {
                        $saldo_qty = $initial_qty + $diff;
                        $saldo_harga = $initial_harga + ($price * $diff);

                        StockLedger::create([
                            'productID' => $productID,
                            'qty' => $diff,
                            'saldo_qty' => $saldo_qty,
                            'saldo_harga' => $saldo_harga,
                            'price' => $price,
                            'total_price' => $price * $diff,
                            'hpp' => $price,
                            'type' => 'Purchase-In',
                            'source_type' => 'PO',
                            'source_id' => $purchaseOrder->purchaseID,
                        ]);
                    } elseif ($diff < 0) {
                        $diff = abs($diff);
                        $saldo_qty = $initial_qty - $diff;
                        $saldo_harga = $initial_harga - ($price * $diff);

                        StockLedger::create([
                            'productID' => $productID,
                            'qty' => -$diff,
                            'saldo_qty' => $saldo_qty,
                            'saldo_harga' => $saldo_harga,
                            'price' => $price,
                            'total_price' => $price * $diff,
                            'hpp' => $price,
                            'type' => 'Purchase-Cancel',
                            'source_type' => 'PO',
                            'source_id' => $purchaseOrder->purchaseID,
                        ]);
                    }
                }

                $totalPrice += $subtotal;
            }

            // 5. Produk lama yang dihapus dari PO
            foreach ($oldDetails as $productID => $oldDetail) {
                if (!in_array($productID, $updatedProductIDs)) {
                    $qty = $oldDetail->quantity;
                    $price = $oldDetail->price;

                    $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                    $saldo_qty = ($last->saldo_qty ?? 0) - $qty;
                    $saldo_harga = ($last->saldo_harga ?? 0) - ($price * $qty);

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => -$qty,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => $price,
                        'total_price' => $price * $qty,
                        'hpp' => $price,
                        'type' => 'Purchase-Cancel',
                        'source_type' => 'PO',
                        'source_id' => $purchaseOrder->purchaseID,
                    ]);
                }
            }

            // Update header PO
            $purchaseOrder->update([
                'purchaseDate' => $request->purchaseDate,
                'Supplier_supplierID' => $request->supplier_id,
                'totalPrice' => $totalPrice,
            ]);
        });

        return redirect()->route('purchase.index')->with('success', 'Purchase Order berhasil diperbarui.');
    }




    public function destroy($id) {
        DB::transaction(function () use ($id) {
            $purchaseorder = Purchaseorder::with('purchasedetails')->findOrFail($id);

            //Tandai semua detail dan header sebagai tidak aktif
            $purchaseorder->purchasedetails()->update(['status' => 0]);
            $purchaseorder->update(['status' => 0]);

            // Ledger pengurangan stok (return-out)
            foreach ($purchaseorder->purchasedetails as $detail) {
                $productID = $detail->Product_productID;
                $qty = $detail->quantity;
                $returned = $detail->returned;
                $price = $detail->price;

                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                $saldo_qty = ($last->saldo_qty ?? 0) - $qty + $returned;
                $saldo_harga = ($last->saldo_harga ?? 0) - ($price * ($qty - $returned));

                StockLedger::create([
                    'productID'     => $productID,
                    'qty'           => -$qty + $returned,
                    'saldo_qty'     => $saldo_qty,
                    'saldo_harga'   => $saldo_harga,
                    'price'         => null,
                    'total_price'   => null,
                    'hpp'           => $price,
                    'type'          => 'Purchase-Cancel',
                    'source_type'   => 'PO',
                    'source_id'     => $purchaseorder->purchaseID,
                ]);
            }
            Returnorder::where('type', 'purchase')
                ->where('sourceID', $purchaseorder->purchaseID)
                ->update(['status' => 0]);

        });

        return redirect()->route('purchase.index')->with('success', 'Purchase Order berhasil dibatalkan.');
    }



}


