<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Salesorder;
use App\Models\Salesdetail;
use App\Models\Returnorder;
use App\Models\StockLedger;
use App\Models\Returndetail;
use Illuminate\Http\Request;
use App\Models\Purchaseorder;
use App\Models\Purchasedetail;
use Illuminate\Support\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ControllerReturn extends Controller {
    public function index(Request $request) {
        // Validasi filter tanggal
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $to = Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();

            if ($from > $to) {
                return back()->withErrors(['from' => 'Bulan 1 tidak boleh setelah bulan 2.'])->withInput();
            }
        }

        // Ambil filter partner
        $partnerID = $request->input('partner_id');
        $type = $request->input('type');

        $partnerPrefix = null;
        $partnerNumericID = null;

        if ($partnerID) {
            [$partnerPrefix, $partnerNumericID] = explode('-', $partnerID);
        }

        // Query Return Order
        $query = Returnorder::where('status', 1)
            // ->orderBy('returnDate', 'desc')
            ->orderBy('returnID', 'desc');

        // Ambil role user
        $userRole = auth()->user()->role;

        // Filter berdasarkan role
        if ($userRole === 'sales') {
            $query->where('type', 'sales'); // hanya return sales
        } elseif ($userRole === 'purchase') {
            $query->where('type', 'purchase'); // hanya return purchase
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($partnerPrefix && $partnerNumericID) {
            if ($partnerPrefix === 'C') {
                $query->where('partnerID', $partnerNumericID)->where('type', 'sales');
            } elseif ($partnerPrefix === 'S') {
                $query->where('partnerID', $partnerNumericID)->where('type', 'purchase');
            }
        }

        if ($request->filled('from')) {
            $from = Carbon::createFromFormat('Y-m', $request->from)->startOfMonth();
            $query->whereDate('returnDate', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::createFromFormat('Y-m', $request->to)->endOfMonth();
            $query->whereDate('returnDate', '<=', $to);
        }

        // Ambil paginasi dan proses partner
        $returnorders = $query->paginate(10)->withQueryString();

        // Tambahkan informasi partner (nama & type SO/PO)
        $returnorders->getCollection()->transform(function ($item) {
            if ($item->type === 'sales') {
                $customer = Customer::find($item->partnerID);
                $item->partner = (object)[
                    'id' => 'C-' . ($customer->customerID ?? ''),
                    'name' => $customer->customerName ?? '-'
                ];
            } else {
                $supplier = Supplier::find($item->partnerID);
                $item->partner = (object)[
                    'id' => 'S-' . ($supplier->supplierID ?? ''),
                    'name' => $supplier->supplierName ?? '-'
                ];
            }

            // Hitung total dari relasi returndetail jika sudah ada (opsional)
            $item->total = $item->returndetail->sum(fn($d) => $d->subtotal ?? 0) ?? 0;

            return $item;
        });

        // Ambil partner list untuk dropdown
        $customers = Customer::where('status', 1)
            ->get()
            ->map(fn($c) => (object)[
                'id' => 'C-' . $c->customerID,
                'name' => $c->customerName,
            ]);

        $suppliers = Supplier::where('status', 1)
            ->get()
            ->map(fn($s) => (object)[
                'id' => 'S-' . $s->supplierID,
                'name' => $s->supplierName,
            ]);

        $partners = $customers->merge($suppliers);

        return view('return.index', [
            'title' => 'Return',
            'user' => 'Nama',
            'returnorders' => $returnorders,
            'partners' => $partners
        ]);
    }

    public function create(Request $request) {
        $type = $request->input('type'); // 'sales' atau 'purchase'
        $partnerID = $request->input('partner_id');

        $partnerPrefix = null;
        $partnerNumericID = null;

        if ($partnerID) {
            [$partnerPrefix, $partnerNumericID] = explode('-', $partnerID);
        }

        $userRole = auth()->user()->role;

        $sales = collect();
        $purchase = collect();

        // Sales Order
        if (($userRole === 'Owner' || $userRole === 'Sales') && (!$type || $type == 'sales')) {
            $sales = Salesorder::with('customer')
                ->where('status', 1)
                ->when($partnerPrefix === 'C' && $partnerNumericID && $type === 'sales', function ($query) use ($partnerNumericID) {
                    $query->where('Customer_customerID', $partnerNumericID);
                })
                ->get()
                ->map(function ($order) {
                    $order->type = 'sales';
                    $order->source_prefix = 'SO';
                    $order->sourceID = $order->salesID;
                    $order->total = $order->totalPrice;
                    $order->date = $order->salesDate;
                    $order->partner_prefix = 'C';
                    $order->partner_name = $order->customer->customerName ?? '-';
                    $order->partner_id = $order->customer->customerID ?? null;
                    return $order;
                });

        }

        // Purchase Order
        if (($userRole === 'Owner' || $userRole === 'Purchase') && (!$type || $type == 'purchase')) {
            $purchase = Purchaseorder::with('supplier')
                ->where('status', 1)
                ->when($partnerPrefix === 'S' && $partnerNumericID && $type === 'purchase', function ($query) use ($partnerNumericID) {
                    $query->where('Supplier_supplierID', $partnerNumericID);
                })
                ->get()
                ->map(function ($order) {
                    $order->type = 'purchase';
                    $order->source_prefix = 'PO';
                    $order->sourceID = $order->purchaseID;
                    $order->total = $order->totalPrice;
                    $order->date = $order->purchaseDate;
                    $order->partner_prefix = 'S';
                    $order->partner_name = $order->supplier->supplierName ?? '-';
                    $order->partner_id = $order->supplier->supplierID ?? null;
                    return $order;
                });

        }

        if ($partnerPrefix === 'C') {
            $purchase = collect(); // kosongkan purchase saat customer difilter
        }
        if ($partnerPrefix === 'S') {
            $sales = collect(); // kosongkan sales saat supplier difilter
        }



        // Gabung dan sort by date
        $orders = $sales->merge($purchase)->sortByDesc('created_at')->values();

        // Pagination
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedData = $orders->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $pagedData,
            $orders->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Partner list dengan prefix
        $customers = Customer::where('status', 1)->select('customerID', 'customerName')->get()
            ->map(fn($c) => (object)[
                'id' => 'C-' . $c->customerID,
                'name' => $c->customerName,
            ]);

        $suppliers = Supplier::where('status', 1)->select('supplierID', 'supplierName')->get()
            ->map(fn($s) => (object)[
                'id' => 'S-' . $s->supplierID,
                'name' => $s->supplierName,
            ]);

        $partners = $customers->merge($suppliers);

        return view('return.create', [
            'title' => 'Return',
            'user' => 'Nama',
            'orders' => $paginated,
            'partners' => $partners,
        ]);
    }

    public function createFormFromSource($type, $id) {
        if ($type === 'sales') {
            $source = Salesorder::with(['customer', 'details.product'])->findOrFail($id);

            $partner_name = $source->customer->customerName ?? '-';
            $source_prefix = 'SO';
            $items = $source->details->map(function ($detail) {
                return (object)[
                    'product_id'         => $detail->Product_productID,  // ← ini penting
                    'product_name'       => $detail->product->productName ?? '-',
                    'quantity' => $detail->quantity,
                    'returned' => $detail->returned ?? 0,

                ];
            });


        } elseif ($type === 'purchase') {
            $source = Purchaseorder::with(['supplier', 'purchasedetails.product'])->findOrFail($id);

            $partner_name = $source->supplier->supplierName ?? '-';
            $source_prefix = 'PO';
            $items = $source->purchasedetails->map(function ($detail) {
                return (object)[
                    'product_id'         => $detail->Product_productID,  // ← ini penting
                    'product_name'       => $detail->product->productName ?? '-',
                    'quantity' => $detail->quantity,
                    'returned' => $detail->returned ?? 0,

                ];
            });


        } else {
            abort(404, 'Invalid return type.');
        }

        return view('return.form', [
            'type' => $type,
            'source_id' => $id,
            'source_prefix' => $source_prefix,
            'partner_name' => $partner_name,
            'items' => $items,
            'title' => 'Return',
            'user' => 'Nama',
        ]);
    }

    public function store(Request $request) {
        // Validasi Data
        $request->validate([
            'type' => 'required|in:sales,purchase',
            'source_id' => 'required|integer',
            'returnDate' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.qty_return' => 'required|integer|min:0',
        ]);

        // Validasi Jumlah Produk Return
        $validItems = collect($request->items)->filter(fn($item) => $item['qty_return'] > 0);
        if ($validItems->isEmpty()) {
            return back()->withErrors(['items' => 'Minimal 1 produk harus direturn.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Sortir Berdasarkan Tipe Transaksi
            $sourceType = $request->type === 'sales' ? 'SO' : 'PO';
            $ledgerType = $request->type === 'sales' ? 'Sales-Return' : 'Purchase-Return';

            // Ambil transaksi utama
            if ($request->type === 'sales') {
                $source = Salesorder::with('details')->findOrFail($request->source_id);
                $partnerID = $source->Customer_customerID;
            } else {
                $source = Purchaseorder::with('purchasedetails')->findOrFail($request->source_id);
                $partnerID = $source->Supplier_supplierID;
            }

            // Simpan header return
            $return = Returnorder::create([
                'type' => $request->type,
                'partnerID' => $partnerID,
                'sourceID' => $request->source_id,
                'returnDate' => $request->returnDate,
                'status' => 1,
            ]);

            // Loop Tiap Item
            foreach ($validItems as $item) {
                $productID = $item['product_id'];
                $qty = $item['qty_return'];

                if ($request->type === 'sales') {
                    $sourceDetail = Salesdetail::where('SalesOrder_salesID', $request->source_id)
                        ->where('Product_productID', $productID)
                        ->first();
                    $costTotal = $sourceDetail->cost / ($sourceDetail->quantity - $sourceDetail->returned) ?? 0; // Field "Cost" di Sales = HPP * Qty, oleh karena itu harus dibagi quantity

                } else {
                    $sourceDetail = Purchasedetail::where('PurchaseOrder_purchaseID', $request->source_id)
                        ->where('Product_productID', $productID)
                        ->first();
                    $costTotal = $sourceDetail->price ?? 0; // Field "Price" di Purchase = Cost Per Product
                }

                $orderedQty = $sourceDetail->quantity ?? 1;
                $unitCost = $orderedQty > 0 ? $costTotal : 0;

                $price = $unitCost;
                $hpp = $unitCost;
                $subtotal = $qty * $price;

                Returndetail::create([
                    'returnID' => $return->returnID,
                    'productID' => $productID,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $last = StockLedger::where('productID', $productID)->latest('created_at')->first();
                $oldQty = $last->saldo_qty ?? 0;
                $oldHarga = $last->saldo_harga ?? 0;


                $qtyChange = $request->type === 'sales' ? $qty : -$qty;
                $newQty = $oldQty + $qtyChange;
                $newHarga = $oldHarga + ($qtyChange * $hpp);

                if ($request->type === 'sales') {

                    // dd($productID,
                    //     $qty,
                    //     $newQty,
                    //     $newHarga,
                    //     $price,
                    //     $qty * $price,
                    //     $hpp,
                    //     $ledgerType,
                    //     $sourceType,
                    //     $request->source_id,);

                    StockLedger::create([
                        'productID'    => $productID,
                        'qty'          => $qty,
                        'saldo_qty'    => $newQty,
                        'saldo_harga'  => $newHarga,
                        'price'        => $price,
                        'total_price'  => $qty * $price,
                        'hpp'          => $hpp,
                        'type'         => $ledgerType,
                        'source_type'  => $sourceType,
                        'source_id'    => $request->source_id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                } else {
                    StockLedger::create([
                        'productID'    => $productID,
                        'qty'          => -$qty,
                        'saldo_qty'    => $newQty,
                        'saldo_harga'  => $newHarga,
                        'price'        => $price,
                        'total_price'  => $price * $qty,
                        'hpp'          => $hpp,
                        'type'         => $ledgerType,
                        'source_type'  => $sourceType,
                        'source_id'    => $request->source_id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                
                
                
                if ($request->type === 'sales') {
                    // Hitung ulang cost sisa (HPP x sisa qty)
                    $remainingQty = $sourceDetail->quantity - $qty - $sourceDetail->returned;
                    $unitHPP = $remainingQty > 0 ? ($sourceDetail->cost /  ($sourceDetail->quantity - $sourceDetail->returned)) : 0;
                    $sourceDetail->cost = $unitHPP * $remainingQty;
                    
                }
                // dd($sourceDetail->quantity, $sourceDetail->returned, $qty, $remainingQty, $unitHPP, $sourceDetail->cost);
                // Update quantity
                $sourceDetail->returned = ($sourceDetail->returned ?? 0) + $qty;

                $sourceDetail->save();
            }

            // Update Header
            if ($request->type === 'sales') {
                $source = Salesorder::with('details')->findOrFail($request->source_id);
                // Hitung ulang Total, HPP, dan Profit
                $salesDetails = Salesdetail::where('SalesOrder_salesID', $request->source_id)->get();

                $totalPrice = $salesDetails->sum(fn($d) => ($d->quantity - $d->returned) * $d->price);
                $totalHPP   = $salesDetails->sum(fn($d) => ($d->quantity - $d->returned) * ($d->cost / max($d->quantity - $d->returned, 1)));
                $totalProfit = $totalPrice - $totalHPP;

                // dd($totalPrice, $totalHPP, $totalProfit);
                $source->totalPrice  = $totalPrice;
                $source->totalHPP    = $totalHPP;
                $source->totalProfit = $totalProfit;
                $source->save();
                

            } else {
                // Hitung ulang Total Pembelian
                $purchaseDetails = Purchasedetail::where('PurchaseOrder_purchaseID', $request->source_id)->get();
                $totalPrice = $purchaseDetails->sum(fn($d) => ($d->quantity - $d->returned) * $d->price);

                $source->totalPrice = $totalPrice;
                $source->save();
            }
            DB::commit();
            return redirect()->route('return.index')->with('success', 'Return dan stok berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }


    public function show($id) {
        $return = Returnorder::with('returndetail.product')->findOrFail($id);

        // Ambil partner
        if ($return->type === 'sales') {
            $partner = Customer::find($return->partnerID);
            $partnerName = $partner->customerName ?? '-';
            $sourcePrefix = 'SO';
        } else {
            $partner = Supplier::find($return->partnerID);
            $partnerName = $partner->supplierName ?? '-';
            $sourcePrefix = 'PO';
        }

        return view('return.show', [
            'return' => $return,
            'partnerName' => $partnerName,
            'sourcePrefix' => $sourcePrefix,
            'title' => 'Detail Return',
            'user' => 'Nama',
        ]);
    }

    public function edit(Returnorder $returnorder)
    {
        
    }

    public function update(Request $request, Returnorder $returnorder)
    {
    
    }

    public function destroy($id) {
        DB::beginTransaction();

        try {
            $return = Returnorder::with('returndetail')->findOrFail($id);

            // Jika sudah dibatalkan
            if ($return->status == 0) {
                return back()->with('error', 'Return ini sudah dibatalkan sebelumnya.');
            }

            $sourceType = $return->type === 'sales' ? 'SO' : 'PO';
            $ledgerType = $return->type === 'sales' ? 'Sales-Return-Reverse' : 'Purchase-Return-Reverse';

            foreach ($return->returndetail as $detail) {
                $productID = $detail->productID;
                $qty       = $detail->quantity;
                $price     = $detail->price;
                $subtotal  = $detail->subtotal;

                // Ambil saldo terakhir stok
                $last = StockLedger::where('productID', $productID)->latest('created_at')->first();
                $oldQty = $last->saldo_qty ?? 0;
                $oldHarga = $last->saldo_harga ?? 0;
                $hpp = $last->hpp ?? $price;

                $qtyChange = $return->type === 'sales' ? -$qty : $qty;
                $newQty = $oldQty + $qtyChange;
                $newHarga = $oldHarga + ($qtyChange * $hpp);

                
                // Simpan ledger pembalik
                StockLedger::create([
                    'productID'    => $productID,
                    'qty'          => $qtyChange,
                    'saldo_qty'    => $newQty,
                    'saldo_harga'  => $newHarga,
                    'price'        => $price,
                    'total_price'  => $subtotal,
                    'hpp'          => $hpp,
                    'type'         => $ledgerType,
                    'source_type'  => $sourceType,
                    'source_id'    => $return->sourceID,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // Update kolom returned dari detail transaksi
                if ($return->type === 'sales') {
                    $sourceDetail = Salesdetail::where('SalesOrder_salesID', $return->sourceID)
                        ->where('Product_productID', $productID)
                        ->first();
                    $sourceDetail->returned = max(0, ($sourceDetail->returned ?? 0) - $qty);
                    // Recalculate cost: cost = unit HPP × (qty - returned)
                    $remainingQty = max(0, $sourceDetail->quantity - $sourceDetail->returned);
                    $unitHPP = ($sourceDetail->quantity-$sourceDetail->returned-$qty) > 0 ? $sourceDetail->cost / ($sourceDetail->quantity-$sourceDetail->returned-$qty) : 0;
                    $sourceDetail->cost = $unitHPP * $remainingQty;
                    $sourceDetail->subtotal = $sourceDetail->price * $remainingQty;
                    $sourceDetail->save();
                  
                } else {
                    $sourceDetail = Purchasedetail::where('PurchaseOrder_purchaseID', $return->sourceID)
                        ->where('Product_productID', $productID)
                        ->first();
                    $sourceDetail->returned = max(0, ($sourceDetail->returned ?? 0) - $qty);
                    // Recalculate cost: cost = unit HPP × (qty - returned)
                    $remainingQty = max(0, $sourceDetail->quantity - $sourceDetail->returned);
                    $sourceDetail->save();
                }

            }
            if ($return->type === 'sales') {
                $salesDetails = Salesdetail::where('SalesOrder_salesID', $return->sourceID)->get();

                $totalPrice = $salesDetails->sum(fn($d) => ($d->quantity - $d->returned) * $d->price);
                $totalHPP   = $salesDetails->sum(fn($d) => ($d->quantity - $d->returned) * ($d->cost / max($d->quantity, 1)));
                $totalProfit = $totalPrice - $totalHPP;

                $salesHeader = Salesorder::find($return->sourceID);
                if ($salesHeader) {
                    $salesHeader->totalPrice  = $totalPrice;
                    $salesHeader->totalHPP    = $totalHPP;
                    $salesHeader->totalProfit = $totalProfit;
                    $salesHeader->save();
                }
            } elseif ($return->type === 'purchase') {
                $purchaseDetails = Purchasedetail::where('PurchaseOrder_purchaseID', $return->sourceID)->get();

                $totalPrice = $purchaseDetails->sum(fn($d) => ($d->quantity - $d->returned) * $d->price);

                $purchaseHeader = Purchaseorder::find($return->sourceID);
                if ($purchaseHeader) {
                    $purchaseHeader->totalPrice = $totalPrice;
                    $purchaseHeader->save();
    }
            }


            // Update status return menjadi dibatalkan
            $return->status = 0;
            $return->save();

            DB::commit();
            return redirect()->route('return.index')->with('success', 'Return telah dibatalkan dan stok dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saat membatalkan return: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


}
