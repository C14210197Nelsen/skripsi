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

        if ($request->filled('isReceived')) {
            $query->where('isReceived', $request->isReceived);
        }

        if ($request->filled('isPaid')) {
            $query->where('isPaid', $request->isPaid);
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

    public function create(Request $request) {
        $suppliers = Supplier::where('status', 1)->get();
        $products  = Product::where('status', 1)->orderBy('productID', 'desc')->get(); 

        $selectedProducts = [];
        if ($request->has('products')) {
            $selected = Product::whereIn('productID', $request->products)->get();

            foreach ($selected as $p) {
                $last = StockLedger::where('productID', $p->productID)->latest('stockledgerID')->first();
                $last_qty = $last->saldo_qty ?? 0;
                $last_cost = $last->saldo_harga ?? 0;


                $hpp = $last_qty > 0 ? $last_cost/$last_qty : 0;

                $selectedProducts[] = [
                    'productCode' => $p->productCode,
                    'productName' => $p->productName,
                    'quantity'    => $request->shortage[$p->productID] ?? 0,
                    'cost'        => $hpp, // ← auto isi HPP terakhir
                ];
            }
        }

        return view('purchase.create', [
            'title'         => 'Purchase',
            'user'          => 'Nama',
            'suppliers'     => $suppliers,
            'products'      => $products,
            'shortageItems' => $selectedProducts,
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
            'description' => 'nullable|string|max:100',
            'isReceived' => 'nullable|boolean',
            'isPaid' => 'nullable|boolean',
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

        DB::beginTransaction();
        try {
            // Simpan header PO
            $purchaseOrder = Purchaseorder::create([
                'purchaseDate' => $request->purchaseDate,
                'Supplier_supplierID' => $request->supplier_id,
                'status' => 1,
                'totalPrice' => 0,
                'description' => $request->description,
                'isReceived' => $request->boolean('isReceived'),
                'isPaid' => $request->boolean('isPaid'),
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
                
                $totalPrice += $subtotal;
                
                if ($purchaseOrder->isReceived) {
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
                }
            }

            // Update total header
            $purchaseOrder->update([
                'totalPrice' => $totalPrice,
            ]);

            DB::commit();
            return redirect()->route('purchase.create')->with('success', 'Transaksi berhasil disimpan.');
        }   catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()])->withInput();
        }
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
        // Validasi input
        $request->validate([
            'supplier_id' => 'required|exists:supplier,supplierID',
            'purchaseDate' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.cost' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:160',
            'isReceived' => 'nullable|boolean',
            'isPaid' => 'nullable|boolean',
        ]);

        // Ambil PO lama + detail
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
                $nonactive[] = $product->productName;
            }

            if (!$isNewProduct && $product->status == 0 && $newQty > $oldQty) {
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

        try {
            DB::transaction(function () use ($request, $purchaseOrder, $oldDetails) {
                $wasReceived = (bool) $purchaseOrder->isReceived;
                $wasPaid = (bool) $purchaseOrder->isPaid;

                $newReceived = $request->boolean('isReceived');
                $newPaid = $request->boolean('isPaid');

                $totalPrice = 0;
                $updatedProductIDs = [];

                foreach ($request->products as $item) {
                    $product = Product::where('productCode', $item['productCode'])->firstOrFail();
                    $productID = $product->productID;
                    $qty = (int) $item['quantity'];
                    $price = $item['cost'];

                    $updatedProductIDs[] = $productID;

                    $returnedQty = $oldDetails[$productID]->returned ?? 0;
                    $oldQty = $oldDetails[$productID]->quantity ?? 0;
                    $effectiveOldQty = max(0, $oldQty - $returnedQty);

                    $subtotal = $qty * $price;

                    // update or create purchasedetail
                    Purchasedetail::updateOrCreate(
                        [
                            'PurchaseOrder_purchaseID' => $purchaseOrder->purchaseID,
                            'Product_productID' => $productID,
                        ],
                        [
                            'quantity' => $qty + $returnedQty,
                            'returned' => $returnedQty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'status' => 1,
                        ]
                    );

                    if ($newReceived) {
                        $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                        $saldo_qty = $last->saldo_qty ?? 0;
                        $saldo_harga = $last->saldo_harga ?? 0;

                        if (!$wasReceived) {
                            // baru diterima
                            $newSaldoQty = $saldo_qty + $qty;
                            $newSaldoHarga = $saldo_harga + ($price * $qty);

                            StockLedger::create([
                                'productID' => $productID,
                                'qty' => $qty,
                                'saldo_qty' => $newSaldoQty,
                                'saldo_harga' => $newSaldoHarga,
                                'price' => $price,
                                'total_price' => $price * $qty,
                                'hpp' => $price,
                                'type' => 'Purchase-In',
                                'source_type' => 'PO',
                                'source_id' => $purchaseOrder->purchaseID,
                            ]);
                        } else {
                            // sudah pernah diterima
                            if ($qty > $effectiveOldQty) {
                                $diff = $qty - $effectiveOldQty;
                                $newSaldoQty = $saldo_qty + $diff;
                                $newSaldoHarga = $saldo_harga + ($price * $diff);

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => $diff,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => $price,
                                    'total_price' => $price * $diff,
                                    'hpp' => $price,
                                    'type' => 'Purchase-In',
                                    'source_type' => 'PO',
                                    'source_id' => $purchaseOrder->purchaseID,
                                ]);
                            } elseif ($qty < $effectiveOldQty) {
                                $price = $saldo_harga/$saldo_qty;
                                $diff = $effectiveOldQty - $qty;
                                $newSaldoQty = $saldo_qty - $diff;
                                $newSaldoHarga = $saldo_harga - ($price * $diff);

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => -$diff,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => $price,
                                    'total_price' => $price * $diff,
                                    'hpp' => $price,
                                    'type' => 'Purchase-Cancel',
                                    'source_type' => 'PO',
                                    'source_id' => $purchaseOrder->purchaseID,
                                ]);
                            }
                        }
                    }

                    $totalPrice += $subtotal;
                }

                // Produk lama yang dihapus
                foreach ($oldDetails as $productID => $oldDetail) {
                    if (!in_array($productID, $updatedProductIDs)) {
                        $oldDetail->update(['status' => 0]);

                        if ($newReceived) {
                            $qtyToCancel = $oldDetail->quantity - ($oldDetail->returned ?? 0);
                            $price = $oldDetail->price;

                            if ($qtyToCancel > 0) {
                                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                                $saldo_qty = $last->saldo_qty ?? 0;
                                $saldo_harga = $last->saldo_harga ?? 0;

                                $price = $saldo_harga/$saldo_qty;

                                $newSaldoQty = $saldo_qty - $qtyToCancel;
                                $newSaldoHarga = $saldo_harga - ($price * $qtyToCancel);

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => -$qtyToCancel,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => $price,
                                    'total_price' => $price * $qtyToCancel,
                                    'hpp' => $price,
                                    'type' => 'Purchase-Cancel',
                                    'source_type' => 'PO',
                                    'source_id' => $purchaseOrder->purchaseID,
                                ]);
                            }
                        }
                    }
                }

                // rollback
                if ($wasReceived && !$newReceived) {
                    foreach ($oldDetails as $productID => $oldDetail) {
                        $qtyToCancel = $oldDetail->quantity - ($oldDetail->returned ?? 0);
                        $price = $oldDetail->price;

                        if ($qtyToCancel > 0) {
                            $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                            $saldo_qty = $last->saldo_qty ?? 0;
                            $saldo_harga = $last->saldo_harga ?? 0;

                            $price = $saldo_harga/$saldo_qty;

                            $newSaldoQty = $saldo_qty - $qtyToCancel;
                            $newSaldoHarga = $saldo_harga - ($price * $qtyToCancel);
                            

                            StockLedger::create([
                                'productID' => $productID,
                                'qty' => -$qtyToCancel,
                                'saldo_qty' => $newSaldoQty,
                                'saldo_harga' => $newSaldoHarga,
                                'price' => $price,
                                'total_price' => $price * $qtyToCancel,
                                'hpp' => $price,
                                'type' => 'Purchase-Cancel',
                                'source_type' => 'PO',
                                'source_id' => $purchaseOrder->purchaseID,
                            ]);
                        }
                    }

                    $purchaseOrder->update([
                        'receivedAt' => null,
                    ]);
                }

                // Update header PO
                $purchaseOrder->update([
                    'purchaseDate' => $request->purchaseDate,
                    'Supplier_supplierID' => $request->supplier_id,
                    'totalPrice' => $totalPrice,
                    'description' => $request->description,
                    'isReceived' => $newReceived ? 1 : 0,
                    'isPaid' => $newPaid ? 1 : 0,
                    'Reference' => $request->reference,
                ]);
            });

            return redirect()->route('purchase.index')->with('success', 'Purchase order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }




    public function destroy($id) {
        DB::transaction(function () use ($id) {
            $purchaseorder = Purchaseorder::with('purchasedetails')->findOrFail($id);

            // Nonaktifkan PO dan detail
            $purchaseorder->purchasedetails()->update(['status' => 0]);
            $purchaseorder->update(['status' => 0]);

            // Kalau belum diterima → stok tidak pernah bergerak → selesai
            if (!$purchaseorder->isReceived) {
                return;
            }

            // Kalau sudah diterima → balikan stok via Purchase-Cancel
            foreach ($purchaseorder->purchasedetails as $detail) {
                $productID = $detail->Product_productID;
                $qty = $detail->quantity;
                $returned = $detail->returned;

                // Hanya cancel qty yang belum direturn
                $cancelQty = $qty - $returned;
                if ($cancelQty <= 0) {
                    continue;
                }

                // Ambil saldo terakhir
                $last = StockLedger::where('productID', $productID)
                    ->latest('stockledgerID')
                    ->first();

                $price = $last->saldo_harga/$last->saldo_qty;
                $costPerUnit = $cancelQty > 0 ? $price : 0;

                $newSaldoQty = ($last->saldo_qty ?? 0) - $cancelQty;
                $newSaldoHarga = ($last->saldo_harga ?? 0) - ($costPerUnit * $cancelQty);

                // Buat ledger cancel
                StockLedger::create([
                    'productID'   => $productID,
                    'qty'         => -$cancelQty,
                    'saldo_qty'   => $newSaldoQty,
                    'saldo_harga' => $newSaldoHarga,
                    'price'       => $costPerUnit,
                    'total_price' => $costPerUnit * $cancelQty,
                    'hpp'         => $costPerUnit,
                    'type'        => 'Purchase-Cancel',
                    'source_type' => 'PO',
                    'source_id'   => $purchaseorder->purchaseID,
                ]);
            }

            // Nonaktifkan return order terkait
            Returnorder::where('type', 'purchase')
                ->where('sourceID', $purchaseorder->purchaseID)
                ->update(['status' => 0]);
        });

        return redirect()->route('purchase.index')->with('success', 'Purchase order berhasil dibatalkan.');
    }



}


