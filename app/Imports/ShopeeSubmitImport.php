<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Salesorder;
use App\Models\Salesdetail;
use App\Models\StockLedger;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\WithEvents;

class ShopeeSubmitImport implements
    ToCollection,
    WithChunkReading,
    WithHeadingRow,
    SkipsEmptyRows,
    WithEvents {
    public array $failedOrders = [];
    public array $failedDetails = [];

    
    private array $bufferOrders = [];
    private ?string $preservedOrderKey = null;

    private Customer $customer;

    // Mapping header (WithHeadingRow akan slugify: "No. Pesanan" -> "no_pesanan", dll)
    private const COL_ORDER_NO    = 'no_pesanan';
    private const COL_STATUS      = 'status_pesanan';
    private const COL_DATE_DONE   = 'waktu_pesanan_dibuat';
    private const COL_PRODUCT     = 'nama_produk';
    private const COL_VARIATION   = 'nama_variasi';
    private const COL_QTY_ORDER   = 'jumlah';
    private const COL_QTY_CANCEL  = 'returned_quantity';
    private const PRICE_KEYS      = ['harga_setelah_diskon'];
    private const COL_DELIVERED_AT = 'waktu_pengiriman_diatur';
    private const COL_PAID_AT      = 'waktu_pembayaran_dilakukan';


    public function __construct() {
        $this->customer = Customer::firstOrCreate(['customerName' => 'Shopee']);
    }

    public function collection(Collection $rows) {
        Log::info("[IMPORT SHOPEE] Chunk diterima, jumlah baris: " . $rows->count());
        foreach ($rows as $row) {
            Log::info("[IMPORT SHOPEE] Row mentah: ", $row->toArray());

            // Pastikan status "selesai"
            $status = strtolower(trim((string)($row[self::COL_STATUS] ?? '')));
            if (!str_contains($status, 'selesai')) {
                Log::info("[IMPORT SHOPEE] Skip row, status bukan selesai: " . $status);
                continue;
            }


            $orderNo = trim((string)($row[self::COL_ORDER_NO] ?? ''));
            if ($orderNo === '') continue;

            // Parse tanggal
            $rawDate = $row[self::COL_DATE_DONE] ?? null;
            $salesDate = $this->parseDate($rawDate); // Y-m-d
            if (!$salesDate) continue;

            $rawDelivered = $row[self::COL_DELIVERED_AT] ?? null;
            $rawPaid      = $row[self::COL_PAID_AT] ?? null;

            $deliveredAt = $this->parseDate($rawDelivered);
            $paidAt      = $this->parseDate($rawPaid);

            if (!isset($this->bufferOrders[$orderNo])) {
                $this->bufferOrders[$orderNo] = [
                    'salesDate'   => $salesDate,
                    'deliveredAt' => $deliveredAt,
                    'paidAt'      => $paidAt,
                    'items'       => [],
                ];
            }

            // Ambil field produk, variasi, qty, price
            $productName = trim((string)($row[self::COL_PRODUCT] ?? ''));
            $variation   = trim((string)($row[self::COL_VARIATION] ?? ''));

            $qtyOrder  = (int)($row[self::COL_QTY_ORDER]  ?? 0);
            $qtyCancel = (int)($row[self::COL_QTY_CANCEL] ?? 0);
            $qty       = $qtyOrder - $qtyCancel;

            $price = (float)$this->pickPrice($row);


            if (!isset($this->bufferOrders[$orderNo])) {
                $this->bufferOrders[$orderNo] = [
                    'salesDate' => $salesDate,
                    'items'     => [],
                ];
            }

            $this->bufferOrders[$orderNo]['items'][] = [
                'productName' => $productName,
                'qty'         => $qty,
                'price'       => $price,
                'variasi'     => $variation,
                'deskripsi'   => $variation, 
            ];
            $this->preservedOrderKey = $orderNo; 
        }

        $this->flushBuffer(preserveLast: true);
    }

    public function chunkSize(): int {
        return 1000; // baca 1000 baris per chunk
    }

    public function registerEvents(): array {
        return [
            AfterImport::class => function () {
                $this->flushBuffer(preserveLast: false);
            },
        ];
    }

    private function pickPrice($row) {
        if ($row instanceof \Illuminate\Support\Collection) {
            $row = $row->toArray();
        }

        $raw = $row['harga_setelah_diskon'] ?? 0;

        // Hapus simbol
        $clean = str_replace(['Rp', ' ', '.', ','], '', (string)$raw);

        return (float) $clean;
    }



    private function parseDate($value): ?string {
        if ($value === null || $value === '') return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning('[IMPORT SHOPEE] Tanggal tidak valid: ' . json_encode($value));
            return null;
        }
    }

    private function flushBuffer(bool $preserveLast): void {
        if (empty($this->bufferOrders)) return;

        $orders = $this->bufferOrders;

        if ($preserveLast && $this->preservedOrderKey && isset($orders[$this->preservedOrderKey])) {
            $last = $this->preservedOrderKey;
            unset($orders[$last]);
            $this->bufferOrders = [$last => $this->bufferOrders[$last]];
        } else {
            $this->bufferOrders = [];
            $this->preservedOrderKey = null;
        }

        if (empty($orders)) return;

        $allNames = [];
        foreach ($orders as $order) {
            foreach ($order['items'] as $it) {
                if (!empty($it['productName'])) $allNames[] = $it['productName'];
            }
        }
        $allNames = array_values(array_unique($allNames));

        $productMap = Product::whereIn('productName', $allNames)
            ->get()
            ->keyBy('productName');

        foreach ($orders as $orderNo => $order) {
            Log::info("[IMPORT SHOPEE] Mulai simpan order: " . $orderNo, $order);

            // ✅ Cek apakah sudah ada SO dengan description mengandung "No. Pesanan: $orderNo"
            $exists = Salesorder::where('status', 1)
                ->where('Reference', $orderNo)
                ->exists();

            if ($exists) {
                Log::warning("[IMPORT SHOPEE] Skip order $orderNo, sudah pernah diimport");

                // 👉 Simpan ke daftar failedOrders biar bisa ditampilkan
                $this->failedOrders[]  = $orderNo;
                $this->failedDetails[] = "Pesanan $orderNo dilewati karena sudah pernah diimport.";

                continue; // skip, jangan buat SO lagi
            }

            DB::beginTransaction();
            try {
                $salesOrder = Salesorder::create([
                    'salesDate'           => $order['salesDate'],
                    'Customer_customerID' => $this->customer->customerID,
                    'status'              => 1,
                    'Reference'           => $orderNo,
                    'description'         => null, // opsional, atau isi variasi saja
                    // Delivery
                    'isDelivered' => 1,
                    'deliveredAt' => $order['deliveredAt'],

                    // Payment
                    'isPaid' => 1,
                    'paidAt' => $order['paidAt'],
                ]);


                $totalPrice = 0.0;
                $totalHPP   = 0.0;
                $variations = [];

                foreach ($order['items'] as $item) {
                    $qty   = (int)($item['qty'] ?? 0);
                    $price = (float)($item['price'] ?? 0.0);
                    $pname = (string)($item['productName'] ?? '');

                    $product = $productMap[$pname] ?? null;
                    if (!$product || $qty <= 0) {
                        Log::warning("[IMPORT SHOPEE] Produk tidak ditemukan / qty <=0", [
                            'orderNo' => $orderNo,
                            'productName' => $pname,
                            'qty' => $qty
                        ]);

                        $this->failedOrders[]  = $orderNo;
                        $this->failedDetails[] = "Pesanan $orderNo: Produk tidak ditemukan atau qty <= 0 - \"{$pname}\"";
                        throw new \Exception("Produk tidak ditemukan atau qty <= 0: {$pname}");
                    }

                    // Ambil cost (dukung dua nama field umum)
                    $cost = $product->productCost ?? ($product->cost ?? 0);
                    $productPrice = $product->productPrice ?? 0; // harga asli dari tabel Product
                    $priceExcel   = $price;                      // harga setelah diskon dari Excel
                    $subtotal     = $qty * $priceExcel;
                    $total_cost = $qty * $cost;

                    // Detail
                    Salesdetail::create([
                        'SalesOrder_salesID' => $salesOrder->salesID,
                        'Product_productID'  => $product->productID,
                        'quantity'           => $qty,
                        'price'              => $priceExcel,
                        'subtotal'           => $subtotal,
                        'status'             => 1,
                        'cost'               => $total_cost,
                        'original_price'     => $productPrice,
                    ]);

                    // Ledger
                    $last = StockLedger::where('productID', $product->productID)
                        ->latest('created_at')->first();

                    $saldo_qty   = ($last->saldo_qty ?? 0)   - $qty;
                    $saldo_harga = ($last->saldo_harga ?? 0) - $total_cost;
                    $hpp         = ($last && $last->saldo_qty > 0)
                        ? ($last->saldo_harga / $last->saldo_qty)
                        : 0;

                    StockLedger::create([
                        'productID'   => $product->productID,
                        'qty'         => -$qty,
                        'saldo_qty'   => $saldo_qty,
                        'saldo_harga' => $saldo_harga,
                        'price'       => null,
                        'total_price' => null,
                        'hpp'         => $hpp,
                        'type'        => 'Sales-Out',
                        'source_type' => 'SO',
                        'source_id'   => $salesOrder->salesID,
                    ]);

                    $totalPrice += $subtotal;
                    $totalHPP   += $total_cost;
                    $variations[] = $item['deskripsi'] ?? $item['variasi'] ?? '';
                }

                $salesOrder->update([
                    'totalPrice'  => $totalPrice,
                    'totalHPP'    => $totalHPP,
                    'totalProfit' => $totalPrice - $totalHPP,
                    'description' => "Variasi: " . implode(', ', array_unique(array_filter($variations))),
                    'Reference'   => $orderNo,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("[IMPORT SHOPEE ERROR] Pesanan $orderNo gagal: " . $e->getMessage());
                continue;
            }
        }
    }
}
