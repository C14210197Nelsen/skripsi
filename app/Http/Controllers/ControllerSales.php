<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Salesorder;
use App\Models\Returnorder;
use App\Models\Salesdetail;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;



use App\Imports\ShopeeSubmitImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class ControllerSales extends Controller {
    public function index(Request $request) {
        // Cek Filter Valid
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $to = Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();

            if ($from > $to) {
                return back()->withErrors(['from' => 'Bulan 1 tidak boleh setelah bulan 2.'])->withInput();
            }
        }

        // Query Database
        $query = Salesorder::with('customer')
            ->where('status', 1)
            // ->orderBy('salesDate', 'desc')
            ->orderBy('salesID', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('Customer_customerID', $request->customer_id);
        }

        if ($request->filled('from')) {
            $from = Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $query->whereDate('salesDate', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();
            $query->whereDate('salesDate', '<=', $to);
        }

        // Filter Delivered
        if ($request->has('delivered') && $request->delivered !== null && $request->delivered !== '') {
            $query->where('isDelivered', $request->delivered);
        }

        // Filter Paid
        if ($request->has('paid') && $request->paid !== null && $request->paid !== '') {
            $query->where('isPaid', $request->paid);
        }


        // Pagination
        $salesorders = $query->paginate(10)->withQueryString();
        $customers = Customer::where('status', 1)->get();

        return view('sales.index', [
            'title' => 'Sales',
            'user' => 'Nama',
            'salesorders' => $salesorders,
            'customers' => $customers,
        ]);
    }

    public function create() {
        // Get Data
        $customers = Customer::where('status', 1)->get();
        $products = Product::where('status', 1)->with('latestStockLedger')->orderBy('productID', 'desc')->get();

        return view('sales.create', [
            'title' => 'Create Sales',
            'user' => 'Nama',
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    public function store(Request $request) {
        // Validasi Data
        $request->validate([
            'customer_id' => 'required|exists:customer,customerID',
            'salesDate' => 'required|date',
            'products' => 'required|array',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'discount_order' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:100',
            'isDelivered' => 'nullable|boolean',
            'isPaid' => 'nullable|boolean',
            'reference' => 'nullable|string|max:100',
            'payment_type' => 'nullable|string|max:50',   // Cash, Transfer, QRIS, dll
            'amount_paid' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
        ]);

        $discount = $request->input('discount_order', 0);

        // Cek Data Produk
        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();

            if (!$product->status) {
                return back()->withErrors([
                    'stok' => "Produk {$product->productCode} tidak aktif dan tidak dapat dijual."
                ])->withInput();
            }

            $latest = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();
            $saldo = $latest->saldo_qty ?? 0;

            if ($saldo < $item['quantity']) {
                return back()->withErrors([
                    'stok' => "Stok produk {$product->productCode} tidak mencukupi. Tersisa $saldo."
                ])->withInput();
            }

            $hpp = ($latest && $latest->saldo_qty > 0) ? $latest->saldo_harga / $latest->saldo_qty : 0;
            if ($item['price'] < $hpp) {
                return back()->withErrors([
                    'harga' => "Harga jual produk {$product->productCode} lebih rendah dari HPP ($hpp)."
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Create SO Header
            $salesOrder = Salesorder::create([
                'salesDate' => $request->salesDate,
                'Customer_customerID' => $request->customer_id,
                'status' => 1,
                'isDelivered' => $request->boolean('isDelivered'),
                'isPaid' => $request->boolean('isPaid'),
                'totalPrice' => 0,
                'totalHPP' => 0,
                'totalProfit' => 0,
                'discount_order' => $discount,
                'description' => $request->description,
                'Reference' => $request->reference,
                'payment_type' => $request->payment_type,
                'amount_paid' => $request->amount_paid,
                'change_amount' => $request->change_amount,
            ]);

            $totalPrice = 0;
            $totalHPP = 0;

            // Create SO Detail
            foreach ($request->products as $item) {
                $product = Product::where('productCode', $item['productCode'])->firstOrFail();
                $qty = $item['quantity'];
                $price = $item['price'];

                // Ambil last ledger untuk hitung HPP
                $last = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();
                $hpp = ($last && $last->saldo_qty > 0) ? $last->saldo_harga / $last->saldo_qty : 0;

                $subtotal = $price * $qty;
                $total_cost = $hpp * $qty;

                // Simpan detail
                Salesdetail::create([
                    'SalesOrder_salesID' => $salesOrder->salesID,
                    'Product_productID' => $product->productID,
                    'quantity' => $qty,
                    'original_price' => $product->productPrice, // harga asli dari master product
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'cost' => $total_cost,
                ]);

                $totalPrice += $subtotal;
                $totalHPP += $total_cost;
            }

            if ($request->boolean('isPaid')) {
                // payment_type wajib diisi
                if (empty($request->payment_type)) {
                    return back()->withErrors([
                        'payment_type' => 'Jenis pembayaran wajib diisi jika status Paid.'
                    ])->withInput();
                }

                // amount_paid tidak boleh kurang dari total
                if ($request->amount_paid < $totalPrice - $discount) {
                    return back()->withErrors([
                        'amount_paid' => 'Jumlah pembayaran kurang dari total harga.'
                    ])->withInput();
                }

                $paymentType = $request->payment_type;
                $amountPaid  = $request->amount_paid;
                $change      = $amountPaid - ($totalPrice - $discount);
            } else {
                $paymentType = null;
                $amountPaid  = 0;
                $change      = 0;
            }

            // Update totals
            $salesOrder->update([
                'totalPrice'   => $totalPrice - $discount,
                'totalHPP'     => $totalHPP,
                'totalProfit'  => $totalPrice - $discount - $totalHPP,
                'payment_type' => $paymentType,
                'amount_paid'  => $amountPaid,
                'change_amount'=> $change,
            ]);

        
            if ($salesOrder->isDelivered) {
                $details = $salesOrder->details()->get();

                foreach ($details as $detail) {
                    $product = $detail->product;
                    $last = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();

                    $prev_saldo_qty = $last->saldo_qty ?? 0;
                    $prev_saldo_harga = $last->saldo_harga ?? 0;

                    $saldo_qty = $prev_saldo_qty - $detail->quantity;
                    $saldo_harga = $prev_saldo_harga - $detail->cost;
                    $hpp = ($detail->quantity > 0) ? $detail->cost / $detail->quantity : 0;

                    
                    if ($saldo_qty < 0) {
                        throw new \Exception("Stok produk {$product->productCode} tidak mencukupi. Tersisa {$prev_saldo_qty}.");
                    }
                    if ($saldo_harga < 0) {
                        throw new \Exception("Saldo harga produk {$product->productCode} menjadi negatif.");
                    }

                    StockLedger::create([
                        'productID' => $product->productID,
                        'qty' => -$detail->quantity,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => null,
                        'total_price' => null,
                        'hpp' => $hpp,
                        'type' => 'Sales-Out',
                        'source_type' => 'SO',
                        'source_id' => $salesOrder->salesID,
                    ]);
                }
            }


            DB::commit();
            return redirect()->route('sales.create')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    public function show($id) {
        $salesorder = Salesorder::with(['customer', 'details.product'])->findOrFail($id);

        return view('sales.show', [
            'title' => 'Sales Order Detail',
            'user' => 'Nama',
            'salesorder' => $salesorder
        ]);
    }

    public function edit($id) {
        $salesorder = Salesorder::with(['customer', 'details.product'])->findOrFail($id);

        $customers = Customer::where('status', 1)
            ->orWhere('customerID', $salesorder->Customer_customerID)
            ->get();

        $products = Product::where('status', 1)->with('latestStockLedger')->orderBy('productID', 'desc')->get();

        return view('sales.edit', [
            'title' => 'Sales Order Edit',
            'user' => 'Nama',
            'salesorder' => $salesorder,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validasi Data
        $request->validate([
            'customer_id' => 'required|exists:customer,customerID',
            'salesDate' => 'required|date',
            'products' => 'required|array',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.price' => 'required|numeric|min:0',
            'discount_order' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:160',
            'isDelivered' => 'nullable|boolean',
            'isPaid' => 'nullable|boolean',
            'reference' => 'nullable|string|max:100',
            'payment_type' => 'nullable|string|max:50',   // Cash, Transfer, QRIS, dll
            'amount_paid' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
        ]);

        // Ambil SO lama & detailnya
        $salesorder = Salesorder::with('details')->findOrFail($id);
        $oldDetails = $salesorder->details->keyBy('Product_productID');

        // Validasi non-active / aturan qty untuk produk nonaktif
        $nonactive = [];
        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
            $productID = $product->productID;

            $isNewProduct = !isset($oldDetails[$productID]); // produk baru
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
                    'products' => "Produk berikut tidak valid untuk SO: " . implode(', ', $nonactive)
                ])
                ->withInput();
        }

        // Cek harga (tetap dicek saat update)
        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();

            $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
            $hpp = ($last && $last->saldo_qty > 0) ? $last->saldo_harga / $last->saldo_qty : 0;

            if ($item['price'] < $hpp) {
                return back()->withErrors([
                    'harga' => "Harga jual produk {$product->productCode} lebih rendah dari HPP ($hpp)."
                ])->withInput();
            }
        }

        // Mulai transaksi DB
        try {
            DB::transaction(function () use ($request, $salesorder, $oldDetails) {
                $wasDelivered = (bool) $salesorder->isDelivered; // status lama
                $wasPaid = (bool) $salesorder->isPaid;

                $newDelivered = $request->boolean('isDelivered');
                $newPaid = $request->boolean('isPaid');
                $paymentType = $salesorder->payment_type;
                $amountPaid  = $salesorder->amount_paid;
                $change      = $salesorder->change_amount;

                foreach ($request->products as $item) {
                    $product = Product::where('productCode', $item['productCode'])->firstOrFail();
                    $productID = $product->productID;
                    $qty = (int) $item['quantity'];

                    $oldQty = $oldDetails[$productID]->quantity ?? 0;
                    $returnedQty = $oldDetails[$productID]->returned ?? 0;
                    $effectiveOldQty = max(0, $oldQty - $returnedQty);

                    $needed = 0;
                    if ($newDelivered && !$wasDelivered) {
                        $needed = $qty;
                    } elseif ($wasDelivered && $newDelivered && $qty > $effectiveOldQty) {
                        $needed = $qty - $effectiveOldQty;
                    }

                    if ($needed > 0) {
                        $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                        $saldo_qty = $last->saldo_qty ?? 0;
                        if ($saldo_qty < $needed) {
                            throw new \Exception("Stok produk {$product->productCode} tidak mencukupi. Tersisa $saldo_qty.");
                        }
                    }
                }


                $discount = $request->input('discount_order', 0);
                $totalPrice = 0;
                $totalHPP = 0;
                $updatedProductIDs = [];
                

                // Update/Insert detail
                foreach ($request->products as $item) {
                    $product = Product::where('productCode', $item['productCode'])->firstOrFail();
                    $productID = $product->productID;
                    $qty = (int) $item['quantity'];
                    $price = $item['price'];

                    $updatedProductIDs[] = $productID;

                    // Ambil last ledger untuk HPP dan saldo
                    $lastLedger = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                    $saldo_qty = $lastLedger->saldo_qty ?? 0;
                    $saldo_harga = $lastLedger->saldo_harga ?? 0;
                    $hpp = ($saldo_qty > 0) ? $saldo_harga / $saldo_qty : 0;
                   
                    // Ambil returned dari old detail jika ada (agar quantity total = qty + returned)
                    $returnedQty = $oldDetails[$productID]->returned ?? 0;
                    $oldQty = $oldDetails[$productID]->quantity ?? 0;
                    $effectiveOldQty = max(0, $oldQty - $returnedQty);

                    $subtotal = $price * $qty;
                    $cost_total = $hpp * $qty;

                    // Update or create salesdetail
                    $detail = Salesdetail::updateOrCreate(
                        [
                            'SalesOrder_salesID' => $salesorder->salesID,
                            'Product_productID' => $productID,
                        ],
                        [
                            'quantity' => $qty + $returnedQty,
                            'returned' => $returnedQty,
                            'price' => $price,
                            'original_price' => $product->productPrice,
                            'subtotal' => $subtotal,
                            'cost' => $cost_total,
                            'status' => 1,
                        ]
                    );

                    // Jika SO akan delivered
                    if ($newDelivered) {

                        $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                        $saldo_qty = $last->saldo_qty ?? 0;

                        $needed = $qty - $oldQty;

                        if ($saldo_qty < $needed) {
                            return back()->withErrors([
                                'stok' => "Stok produk {$product->productCode} tidak mencukupi. Tersisa $saldo_qty."
                            ])->withInput();
                        }

                        // Jika state berubah dari 0 -> 1
                        if (!$wasDelivered) {
                            $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                            $current_saldo_qty = $last->saldo_qty ?? 0;
                            $current_saldo_harga = $last->saldo_harga ?? 0;

                            $newSaldoQty = $current_saldo_qty - $qty;
                            $newSaldoHarga = $current_saldo_harga - ($hpp * $qty);

                            if ($newSaldoQty < 0) {
                                throw new \Exception("Stok produk {$product->productCode} tidak mencukupi. Tersisa {$current_saldo_qty}.");
                            }
                            if ($newSaldoHarga < 0) {
                                throw new \Exception("Saldo harga produk {$product->productCode} menjadi negatif.");
                            }

                            StockLedger::create([
                                'productID' => $productID,
                                'qty' => -$qty,
                                'saldo_qty' => $newSaldoQty,
                                'saldo_harga' => $newSaldoHarga,
                                'price' => null,
                                'total_price' => null,
                                'hpp' => ($qty > 0) ? ($hpp) : 0,
                                'type' => 'Sales-Out',
                                'source_type' => 'SO',
                                'source_id' => $salesorder->salesID,
                            ]);
                        } else {
                            // Jika sudah delivered sebelumnya -> buat ledger berdasarkan selisih
                            if ($qty > $effectiveOldQty) {
                                $diff = $qty - $effectiveOldQty;

                                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                                $current_saldo_qty = $last->saldo_qty ?? 0;
                                $current_saldo_harga = $last->saldo_harga ?? 0;

                                $newSaldoQty = $current_saldo_qty - $diff;
                                $newSaldoHarga = $current_saldo_harga - ($hpp * $diff);

                                if ($newSaldoQty < 0) {
                                    throw new \Exception("Stok produk {$product->productCode} tidak mencukupi untuk penambahan qty.");
                                }
                                if ($newSaldoHarga < 0) {
                                    throw new \Exception("Saldo harga produk {$product->productCode} menjadi negatif.");
                                }

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => -$diff,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => null,
                                    'total_price' => null,
                                    'hpp' => ($diff > 0) ? $hpp : 0,
                                    'type' => 'Sales-Out',
                                    'source_type' => 'SO',
                                    'source_id' => $salesorder->salesID,
                                ]);
                            } elseif ($qty < $effectiveOldQty) {
                                $diff = $effectiveOldQty - $qty;
                                $hpp = ($oldDetails[$productID]->quantity > 0) ? $oldDetails[$productID]->cost / ($oldDetails[$productID]->quantity - $oldDetails[$productID]->returned) : 0;


                                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                                $current_saldo_qty = $last->saldo_qty ?? 0;
                                $current_saldo_harga = $last->saldo_harga ?? 0;

                                $newSaldoQty = $current_saldo_qty + $diff;
                                $newSaldoHarga = $current_saldo_harga + ($hpp * $diff);

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => $diff,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => $hpp,
                                    'total_price' => $hpp * $diff,
                                    'hpp' => $hpp,
                                    'type' => 'Sales-Cancel',
                                    'source_type' => 'SO',
                                    'source_id' => $salesorder->salesID,
                                ]);
                            }
                        }
                    }
                    $totalPrice += $subtotal;
                    $totalHPP += $cost_total;
                }

                // Produk lama yang dihapus
                foreach ($oldDetails as $productID => $oldDetail) {
                    if (!in_array($productID, $updatedProductIDs)) {
                        // tandai nonaktif detail
                        $oldDetail->update(['status' => 0]);

                        // rollback stok
                        if ($newDelivered) {
                            $qtyToReturn = $oldDetail->quantity - ($oldDetail->returned ?? 0);
                            if ($qtyToReturn > 0) {
                                $hpp = ($oldDetails[$productID]->quantity > 0) ? $oldDetails[$productID]->cost / ($oldDetails[$productID]->quantity - $oldDetails[$productID]->returned) : 0;

                                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                                $current_saldo_qty = $last->saldo_qty ?? 0;
                                $current_saldo_harga = $last->saldo_harga ?? 0;
                                $costPerUnit = ($oldDetail->cost ?? 0) / max(1, ($oldDetail->quantity - ($oldDetail->returned ?? 0)));

                                $newSaldoQty = $current_saldo_qty + $qtyToReturn;
                                $newSaldoHarga = $current_saldo_harga + ($costPerUnit * $qtyToReturn);

                                StockLedger::create([
                                    'productID' => $productID,
                                    'qty' => $qtyToReturn,
                                    'saldo_qty' => $newSaldoQty,
                                    'saldo_harga' => $newSaldoHarga,
                                    'price' => $costPerUnit,
                                    'total_price' => $costPerUnit * $qtyToReturn,
                                    'hpp' => $costPerUnit,
                                    'type' => 'Sales-Cancel',
                                    'source_type' => 'SO',
                                    'source_id' => $salesorder->salesID,
                                ]);
                            }
                        }
                    }
                }

                // Jika wasDelivered == 1 jadi not delivered
                if ($wasDelivered && !$newDelivered) {
                    foreach ($oldDetails as $productID => $oldDetail) {
                        $qtyToReturn = $oldDetail->quantity - ($oldDetail->returned ?? 0);
                        if ($qtyToReturn <= 0) continue;

                        $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                        $current_saldo_qty = $last->saldo_qty ?? 0;
                        $current_saldo_harga = $last->saldo_harga ?? 0;
                        $costPerUnit = ($oldDetail->cost ?? 0) / max(1, ($oldDetail->quantity - ($oldDetail->returned ?? 0)));

                        $newSaldoQty = $current_saldo_qty + $qtyToReturn;
                        $newSaldoHarga = $current_saldo_harga + ($costPerUnit * $qtyToReturn);

                        StockLedger::create([
                            'productID' => $productID,
                            'qty' => $qtyToReturn,
                            'saldo_qty' => $newSaldoQty,
                            'saldo_harga' => $newSaldoHarga,
                            'price' => $costPerUnit,
                            'total_price' => $costPerUnit * $qtyToReturn,
                            'hpp' => $costPerUnit,
                            'type' => 'Sales-Cancel',
                            'source_type' => 'SO',
                            'source_id' => $salesorder->salesID,
                        ]);
                    }

                    $salesorder->update([
                        'deliveredAt' => null,
                    ]);
                    
                }

                if (!$wasPaid && $newPaid) {
                    // validasi wajib isi payment_type
                    if (empty($request->payment_type)) {
                        throw new \Exception("Jenis pembayaran wajib diisi jika status Paid.");
                    }

                    $orderTotal = $totalPrice - $discount;

                    if ($request->amount_paid < $orderTotal) {
                        throw new \Exception("Jumlah pembayaran kurang dari total harga.");
                    }

                    $paymentType = $request->payment_type;
                    $amountPaid  = $request->amount_paid;
                    $change      = $amountPaid - $orderTotal;
                }

                if ($wasPaid && !$newPaid) {
                    $paymentType = null;
                    $amountPaid  = 0;
                    $change      = 0;
                }

                // 5Update header SO (totals & flags)
                $salesorder->update([
                    'salesDate' => $request->salesDate,
                    'Customer_customerID' => $request->customer_id,
                    'totalPrice' => $totalPrice - $discount,
                    'totalHPP' => $totalHPP,
                    'discount_order' => $discount,
                    'totalProfit' => $totalPrice - $totalHPP - $discount,
                    'description' => $request->description,
                    'isDelivered' => $newDelivered ? 1 : 0,
                    'isPaid' => $newPaid ? 1 : 0,
                    'Reference' => $request->reference,
                    'payment_type' => $paymentType,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $change,
                ]);
            }); 

            return redirect()->route('sales.index')->with('success', 'Sales order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }


    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $salesorder = Salesorder::with('details')->findOrFail($id);

            // Nonaktifkan SO dan detail
            $salesorder->details()->update(['status' => 0]);
            $salesorder->update(['status' => 0]);

            // Kalau belum delivered → stok tidak pernah bergerak → selesai
            if (!$salesorder->isDelivered) {
                return;
            }

            // Kalau sudah delivered → balikan stok via Sales-Cancel
            foreach ($salesorder->details as $detail) {
                $productID = $detail->Product_productID;
                $qty = $detail->quantity;
                $returned = $detail->returned;

                // Hanya cancel qty yang belum direturn
                $cancelQty = $qty - $returned;
                if ($cancelQty <= 0) {
                    continue;
                }

                // Hitung HPP per unit
                $costPerUnit = $cancelQty > 0 ? $detail->cost / $cancelQty : 0;

                // Ambil saldo terakhir
                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                $newSaldoQty = ($last->saldo_qty ?? 0) + $cancelQty;
                $newSaldoHarga = ($last->saldo_harga ?? 0) + ($costPerUnit * $cancelQty);

                // Buat ledger cancel
                StockLedger::create([
                    'productID'   => $productID,
                    'qty'         => $cancelQty,
                    'saldo_qty'   => $newSaldoQty,
                    'saldo_harga' => $newSaldoHarga,
                    'price'       => $costPerUnit,
                    'total_price' => $costPerUnit * $cancelQty,
                    'hpp'         => $costPerUnit,
                    'type'        => 'Sales-Cancel',
                    'source_type' => 'SO',
                    'source_id'   => $salesorder->salesID,
                ]);
            }

            // Nonaktifkan return order terkait
            Returnorder::where('type', 'sales')
                ->where('sourceID', $salesorder->salesID)
                ->update(['status' => 0]);
        });

        return redirect()->route('sales.index')->with('success', 'Sales order berhasil dibatalkan.');
    }



    public function printInvoice($id)
    {
        $salesorder = SalesOrder::with(['customer', 'details.product'])->findOrFail($id);

        return view('sales.print-invoice', compact('salesorder'));
    }



}