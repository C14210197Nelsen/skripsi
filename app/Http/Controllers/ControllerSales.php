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
            'description' => 'nullable|string|max:100'
        ]);

        $discount = $request->input('discount_order', 0); // Ambil nilai diskon dari form

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
        }

        // Create SO Header
        $salesOrder = Salesorder::create([
            'salesDate' => $request->salesDate,
            'Customer_customerID' => $request->customer_id,
            'status' => 1,
            'totalPrice' => 0,
            'totalHPP' => 0,
            'totalProfit' => 0,
            'discount_order' => $discount,
            'description' => $request->description
        ]);

        $totalPrice = 0;
        $totalHPP = 0;

        // Create SO Detail & Stock Ledger
        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
            $qty = $item['quantity'];
            $price = $item['price'];

            $last = StockLedger::where('productID', $product->productID)->latest('stockledgerID')->first();
            $hpp = ($last && $last->saldo_qty > 0) ? $last->saldo_harga / $last->saldo_qty : 0;

            $subtotal = $price * $qty;
            $total_cost = $hpp * $qty;

            Salesdetail::create([
                'SalesOrder_salesID' => $salesOrder->salesID,
                'Product_productID' => $product->productID,
                'quantity' => $qty,
                'original_price' => $product->productPrice, // harga asli dari master product
                'price' => $price,
                'subtotal' => $subtotal,
                'cost' => $total_cost,

            ]);

            $saldo_qty = $last->saldo_qty - $qty;
            $saldo_harga = $last->saldo_harga - $total_cost;

            StockLedger::create([
                'productID' => $product->productID,
                'qty' => -$qty,
                'saldo_qty' => $saldo_qty,
                'saldo_harga' => $saldo_harga,
                'price' => null,
                'total_price' => null,
                'hpp' => $hpp,
                'type' => 'Sales-Out',
                'source_type' => 'SO',
                'source_id' => $salesOrder->salesID,
            ]);

            $totalPrice += $subtotal;
            $totalHPP += $total_cost;
        }

        $salesOrder->update([
            'totalPrice' => $totalPrice - $discount,
            'totalHPP' => $totalHPP,
            'totalProfit' => $totalPrice - $totalHPP - $discount,
        ]);

        return redirect()->route('sales.create')->with('success', 'Transaksi berhasil disimpan.');
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

    public function update(Request $request, $id) {

        // Validasi Data
        $request->validate([
            'customer_id' => 'required|exists:customer,customerID',
            'salesDate' => 'required|date',
            'products' => 'required|array',
            'products.*.productCode' => 'required|exists:product,productCode',
            'products.*.quantity' => 'required|integer|min:0',
            'products.*.price' => 'required|numeric|min:0',
            'discount_order' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:160'
        ]);

        // Ambil SO lama & detailnya
        $salesorder = Salesorder::with('details')->findOrFail($id);
        $oldDetails = $salesorder->details->keyBy('Product_productID');

        $nonactive = [];

        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
            $productID = $product->productID;

            $isNewProduct = !isset($oldDetails[$productID]); // produk baru
            $oldQty = $oldDetails[$productID]->quantity ?? 0;
            $newQty = $item['quantity'];

            // Produk baru tapi nonaktif
            if ($isNewProduct && $product->status == 0) {
                $nonactive[] = $product->productName;
            }

            // Produk lama nonaktif tapi qty bertambah
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

        // Cek stok
        foreach ($request->products as $item) {
            $product = Product::where('productCode', $item['productCode'])->firstOrFail();
            
            $qty = $item['quantity'];
            $productID = $product->productID;

            $oldQty = $oldDetails[$productID]->quantity ?? 0;
            $needed = $qty - $oldQty;

            if ($needed > 0) {
                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                $saldo_qty = $last->saldo_qty ?? 0;

                if ($saldo_qty < $needed) {
                    return back()->withErrors([
                        'stok' => "Stok produk {$product->productCode} tidak mencukupi. Tersisa $saldo_qty."
                    ])->withInput();
                }
            }
        }


        DB::transaction(function () use ($request, $id) {
            $salesorder = Salesorder::with('details')->findOrFail($id);
            $oldDetails = $salesorder->details->keyBy('Product_productID');
            $salesorder->details()->delete();

            $totalPrice = 0;
            $totalHPP = 0;
            $discount = $request->input('discount_order', 0);

            $updatedProductIDs = [];

            // Membuat SO Detail dan Stock Ledger Baru
            foreach ($request->products as $item) {
                $product = Product::where('productCode', $item['productCode'])->firstOrFail();
                $qty = $item['quantity'];
                $price = $item['price'];
                $productID = $product->productID;
                $updatedProductIDs[] = $productID;

                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                $saldo_qty = $last->saldo_qty ?? 0;
                $saldo_harga = $last->saldo_harga ?? 0;

 
                $oldQty = $oldDetails[$productID]->quantity ?? 0;
                $returnedQty = $oldDetails[$productID]->returned ?? 0;
                $oldQty -= $returnedQty;

                $hpp = ($saldo_qty > 0) ? $saldo_harga / $saldo_qty : 0;
                $subtotal = $price * $qty;
                $cost_total = $hpp * $qty;

                Salesdetail::create([
                    'SalesOrder_salesID' => $salesorder->salesID,
                    'Product_productID' => $productID,
                    'quantity' => $qty + $returnedQty,
                    'returned' => $returnedQty,
                    'price' => $price,
                    'original_price' => $product->productPrice,
                    'subtotal' => $subtotal,
                    'cost' => $cost_total,
                    'status' => 1,

                ]);

                if ($qty > $oldQty) {
                    // Out selisihnya
                    $diff = $qty - $oldQty;
                    $saldo_qty -= $diff;
                    $saldo_harga -= $hpp * $diff;

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => -$diff,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => null,
                        'total_price' => null,
                        'hpp' => $hpp,
                        'type' => 'Sales-Out',
                        'source_type' => 'SO',
                        'source_id' => $salesorder->salesID,
                    ]);
                } elseif ($qty < $oldQty) {
                    // Return selisihnya
                    $diff = $oldQty - $qty;
                    $saldo_qty += $diff;
                    $saldo_harga += $hpp * $diff;

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => $diff,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => $hpp,
                        'total_price' => $hpp * $diff,
                        'hpp' => $hpp,
                        'type' => 'Sales-Cancel',
                        'source_type' => 'SO',
                        'source_id' => $salesorder->salesID,
                    ]);
                } elseif ($oldQty == 0) {
                    // New item (belum pernah ada di SO ini)
                    $saldo_qty -= $qty;
                    $saldo_harga -= $hpp * $qty;

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => -$qty,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => null,
                        'total_price' => null,
                        'hpp' => $hpp,
                        'type' => 'Sales-Out',
                        'source_type' => 'SO',
                        'source_id' => $salesorder->salesID,
                    ]);
                }

                $totalPrice += $subtotal;
                $totalHPP += $cost_total;
            }

            // Jika ada item lama yang dihapus
            foreach ($oldDetails as $productID => $oldDetail) {
                if (!in_array($productID, $updatedProductIDs)) {
                    $qty = $oldDetail->quantity;
                    $cost = $oldDetail->cost / $qty;

                    $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();
                    $saldo_qty = ($last->saldo_qty ?? 0) + $qty;
                    $saldo_harga = ($last->saldo_harga ?? 0) + ($cost * $qty);

                    StockLedger::create([
                        'productID' => $productID,
                        'qty' => $qty,
                        'saldo_qty' => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price' => $cost,
                        'total_price' => $cost * $qty,
                        'hpp' => $cost,
                        'type' => 'Sales-Cancel',
                        'source_type' => 'SO',
                        'source_id' => $salesorder->salesID,
                    ]);
                }
            }

            $salesorder->update([
                'salesDate' => $request->salesDate,
                'Customer_customerID' => $request->customer_id,
                'totalPrice' => $totalPrice - $discount,
                'totalHPP' => $totalHPP,
                'discount_order' => $discount,
                'totalProfit' => $totalPrice - $totalHPP - $discount,
                'description' => $request->description
            ]);
        });

        return redirect()->route('sales.index')->with('success', 'Sales order berhasil diperbarui.');
    }



    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $salesorder = Salesorder::with('details')->findOrFail($id);

            // Detail dan salesorder tidak aktif
            $salesorder->details()->update(['status' => 0]);
            $salesorder->update(['status' => 0]);

            // Ambil semua detail untuk dibuat ledger return
            foreach ($salesorder->details as $detail) {
                $productID = $detail->Product_productID;
                $qty = $detail->quantity;
                $returned = $detail->returned;
                $cost = ($qty - $returned) != 0 ? $detail->cost / ($qty - $returned) : 0;

                // dd($productID, $qty, $returned, $detail->cost,  $qty - $returned);
  

                // $last = StockLedger::where('productID', $productID)->latest('created_at')->first(); // ini sudah benar, namun saat bikin1 SO dengan 2 item yang sama, lalu delete. stockledgernya salah ambil latest 2 karena created atnya sama
                $last = StockLedger::where('productID', $productID)->latest('stockledgerID')->first();

                $newSaldoQty = ($last->saldo_qty ?? 0) + ($qty - $returned);
                $newSaldoHarga = ($last->saldo_harga ?? 0) + ($cost * ($qty - $returned));
                // dd ($last->saldo_qty, $qty, $returned, $newSaldoQty);
                // Masukkan ledger return
                StockLedger::create([
                    'productID'     => $productID,
                    'qty'           => $qty - $returned,
                    'saldo_qty'     => $newSaldoQty,
                    'saldo_harga'   => $newSaldoHarga,
                    'price'         => $cost,
                    'total_price'   => $cost * ($qty - $returned),
                    'hpp'           => $cost,
                    'type'          => 'Sales-Cancel',
                    'source_type'   => 'SO',
                    'source_id'     => $salesorder->salesID,
                ]);
            }

            Returnorder::where('type', 'sales')
                ->where('sourceID', $salesorder->salesID)
                ->update(['status' => 0]);

        });

        return redirect()->route('sales.index')->with('success', 'Sales order berhasil dibatalkan.');
    }
    
    // public function import(Request $request)
    // {
    //     $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls']);

    //     $rows = Excel::toArray([], $request->file('excel_file'))[0]; // sheet pertama

    //     DB::beginTransaction();
    //     try {
    //         $customer = Customer::firstOrCreate(['customerName' => 'Shopee']);
    //         $processedOrders = [];
    //         $variationsMap = []; // Menyimpan semua variasi berdasarkan No. Pesanan

    //         foreach ($rows as $index => $row) {
    //             if ($index === 0 || strtolower(trim($row[1])) != 'selesai') continue;

    //             $noPesanan = trim($row[0]);
    //             $salesDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[9])->format('d-m-Y');
    //             $productName = trim($row[13]);
    //             $productQty = (int)$row[18] - (int)$row[19];
    //             $priceAfterDiscount = floatval($row[17]);
    //             $totalDiscount = floatval($row[21] ?? 0);
    //             $namaVariasi = $row[15] ?? null;

    //             if ($productQty <= 0) continue;

    //             $product = Product::where('productName', $productName)->first();
    //             if (!$product) {
    //                 Log::warning("Produk tidak ditemukan saat import Shopee: " . $productName . " (Baris Excel: " . ($index + 1) . ")");
    //                 continue;
    //             }

    //             // DEBUG LOG - Cek apakah price dan cost tersedia
    //             Log::info("Cek Produk", [
    //                 'productID' => $product->productID,
    //                 'productName' => $product->productName,
    //                 'price' => $product->price,
    //                 'cost' => $product->cost
    //             ]);

    //             // Tangani jika price atau cost null
    //             $originalPrice = $product->productPrice ?? $priceAfterDiscount; // fallback ke harga diskon
    //             $cost = $product->productCost ?? 0;

    //             // Jika salesorder untuk No. Pesanan ini belum dibuat
    //             if (!isset($processedOrders[$noPesanan])) {
    //                 $salesorder = Salesorder::create([
    //                     'salesDate'           => $salesDate,
    //                     'Customer_customerID' => $customer->customerID,
    //                     'status'              => 1,
    //                     'description'         => 'No. Pesanan: ' . $noPesanan, // sementara
    //                 ]);
    //                 Log::info('Salesorder dibuat', ['salesID' => $salesorder->salesID]);
    //                 $processedOrders[$noPesanan] = $salesorder;
    //                 $variationsMap[$noPesanan] = [];
    //             } else {
    //                 $salesorder = $processedOrders[$noPesanan];
    //             }

    //             // Tambahkan sales detail
    //             Salesdetail::create([
    //                 'SalesOrder_salesID' => $salesorder->salesID,
    //                 'Product_productID'  => $product->productID,
    //                 'original_price'     => $originalPrice,
    //                 'quantity'           => $productQty,
    //                 'price'              => $priceAfterDiscount,
    //                 'subtotal'           => $priceAfterDiscount * $productQty,
    //                 'status'             => 1,
    //                 'cost'               => $cost,
    //             ]);

    //             // Ambil stok terakhir dari Stock Ledger
    //             $lastLedger = Stockledger::where('productID', $product->productID)->latest('created_at')->first();

    //             // Hitung HPP (jika stok sebelumnya ada)
    //             $hpp = ($lastLedger && $lastLedger->saldo_qty > 0) ? $lastLedger->saldo_harga / $lastLedger->saldo_qty : 0;

    //             // Hitung saldo baru
    //             $subtotal = $priceAfterDiscount * $productQty;
    //             $total_cost = $hpp * $productQty;

    //             $saldo_qty = $lastLedger ? ($lastLedger->saldo_qty - $productQty) : -$productQty;
    //             $saldo_harga = $lastLedger ? ($lastLedger->saldo_harga - $total_cost) : -$total_cost;

    //             // Tambahkan ke Stock Ledger
    //             Stockledger::create([
    //                 'productID'     => $product->productID,
    //                 'qty'           => -$productQty,
    //                 'saldo_qty'     => $saldo_qty,
    //                 'saldo_harga'   => $saldo_harga,
    //                 'price'         => null,
    //                 'total_price'   => null,
    //                 'hpp'           => $hpp,
    //                 'type'          => 'Sales-Out',
    //                 'source_type'   => 'SO',
    //                 'source_id'     => $salesorder->salesID,
    //             ]);


    //             // Kumpulkan variasi untuk update di akhir
    //             if (!empty($namaVariasi)) {
    //                 $variationsMap[$noPesanan][] = $namaVariasi;
    //             }
    //         }

    //         // Update total dan description untuk setiap salesorder
    //         foreach ($processedOrders as $noPesanan => $salesorder) {
    //             $salesorder->totalPrice = $salesorder->details->sum('subtotal');
    //             $salesorder->totalHPP = $salesorder->details->sum(fn ($d) => $d->cost * $d->quantity);
    //             $salesorder->totalProfit = $salesorder->totalPrice - $salesorder->totalHPP;

    //             $uniqueVariasi = implode(', ', array_unique($variationsMap[$noPesanan]));
    //             $salesorder->description = "No. Pesanan: $noPesanan | Variasi: $uniqueVariasi";
    //             $salesorder->save();
    //         }

    //         Log::info('Shopee import berhasil, akan commit...');
    //         DB::commit();
    //         return back()->with('success', 'Import data Shopee berhasil!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Shopee import gagal: ' . $e->getMessage());
    //         return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
    //     }
    // }

    // public function previewShopee(Request $request) {
    //     ini_set('max_execution_time', 300);
    //     ini_set('memory_limit', '1024M');

    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls'
    //     ]);

    //     $import = new \App\Imports\ShopeePreviewImport();
    //     \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('excel_file'));

    //     $products = \App\Models\Product::where('status', 1)->get();

    //     return view('sales.preview-import', [
    //         'title' => 'Sales Import Preview',
    //         'orders' => $import->orders,
    //         'jsonData' => json_encode($import->orders),
    //         'products' => $products,
    //     ]);
    // }



    // public function submitShopee(Request $request) {
    //     ini_set('max_execution_time', 600);
    //     ini_set('memory_limit', '1024M');

    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls'
    //     ]);

    //     $import = new ShopeeSubmitImport();
    //     Excel::import($import, $request->file('excel_file'));

    //     if (!empty($import->failedDetails)) {
    //         return redirect()->route('sales.index')
    //             ->with('warning', 'Beberapa pesanan gagal diimpor:<br>' . implode('<br>', $import->failedDetails));
    //     }

    //     return redirect()->route('sales.index')
    //         ->with('success', 'Semua pesanan berhasil diimpor!');
    // }



    public function printInvoice($id)
    {
        $salesorder = SalesOrder::with(['customer', 'details.product'])->findOrFail($id);

        return view('sales.print-invoice', compact('salesorder'));
    }



}