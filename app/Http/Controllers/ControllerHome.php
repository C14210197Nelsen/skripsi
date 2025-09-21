<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ControllerHome extends Controller {
    public function index(Request $request) {
        // Filter by Month
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // User
        $user = Auth::user();

        // Sales
        $totalSales = DB::table('salesorder')
            ->whereMonth('salesDate', $bulan)
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->sum('totalPrice');

        $orderCount = DB::table('salesorder')
            ->whereMonth('salesDate', $bulan)
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->count();

        // Returned Qty
        $returned = DB::table('returndetail as rd')
            ->join('returnorder as ro', 'rd.returnID', '=', 'ro.returnID')
            ->join('product as p', 'rd.productID', '=', 'p.productID')
            ->select('p.productName', DB::raw('SUM(rd.quantity) as total_returned'))
            ->where('ro.type', 'sales')
            ->whereMonth('ro.returnDate', $bulan)
            ->whereYear('ro.returnDate', $tahun)
            ->where('ro.status', 1)
            ->where('p.status', 1)
            ->groupBy('p.productName')
            ->orderByDesc('total_returned')
            ->get();

        // Return rate
        $totalReturnedQty = $returned->sum('total_returned');
        $totalSoldQty = DB::table('salesdetail as sd')
            ->join('salesorder as so', 'sd.SalesOrder_salesID', '=', 'so.salesID')
            ->whereMonth('so.salesDate', $bulan)
            ->whereYear('so.salesDate', $tahun)
            ->where('so.status', 1)
            ->sum('sd.quantity');

        $returnRate = $totalSoldQty > 0
            ? round(($totalReturnedQty / $totalSoldQty) * 100, 2)
            : 0;

        // Top products (qty & sales)
        $topProducts = DB::table('salesdetail as sd')
            ->join('salesorder as so', 'sd.SalesOrder_salesID', '=', 'so.salesID')
            ->join('product as p', 'sd.Product_productID', '=', 'p.productID')
            ->select(
                'p.productName',
                DB::raw('SUM(sd.quantity) as total_qty'),
                DB::raw('SUM(sd.subtotal) as total_sales')
            )
            ->whereMonth('so.salesDate', $bulan)
            ->whereYear('so.salesDate', $tahun)
            ->where('so.status', 1)
            ->where('p.status', 1)
            ->groupBy('p.productName')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Growth vs last month
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunLalu = $bulan == 1 ? $tahun - 1 : $tahun;

        $salesBulanLalu = DB::table('salesorder')
            ->whereMonth('salesDate', $bulanLalu)
            ->whereYear('salesDate', $tahunLalu)
            ->where('status', 1)
            ->sum('totalPrice');

        $growth = $salesBulanLalu > 0
            ? round((($totalSales - $salesBulanLalu) / $salesBulanLalu) * 100, 2)
            : null;

        // Purchase 
        $totalPurchase = DB::table('purchaseorder')
            ->whereMonth('purchaseDate', $bulan)
            ->whereYear('purchaseDate', $tahun)
            ->where('status', 1)
            ->sum('totalPrice');

        $purchaseDetails = DB::table('purchasedetail as pd')
            ->join('purchaseorder as po', 'pd.PurchaseOrder_purchaseID', '=', 'po.purchaseID')
            ->join('product as p', 'pd.Product_productID', '=', 'p.productID')
            ->select(
                'p.productName',
                DB::raw('SUM(pd.quantity) as total_qty'),
                DB::raw('SUM(pd.subtotal) as total_value')
            )
            ->whereMonth('po.purchaseDate', $bulan)
            ->whereYear('po.purchaseDate', $tahun)
            ->where('po.status', 1)
            ->where('p.status', 1)
            ->groupBy('p.productName')
            ->orderByDesc('total_qty')
            ->get();

        // Finance
        $rekapPemasukan = DB::table('rekapan')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('tipe', 'pemasukan')
            ->sum('jumlah');

        $rekapPengeluaran = DB::table('rekapan')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('tipe', 'pengeluaran')
            ->sum('jumlah');

        $cashflow = [
            'Sales'             => $totalSales,
            'Purchase'          => $totalPurchase,
            'Rekap Pemasukan'   => $rekapPemasukan,
            'Rekap Pengeluaran' => $rekapPengeluaran,
            'NetCashflow'       => ($totalSales + $rekapPemasukan) - ($totalPurchase + $rekapPengeluaran)
        ];

        $totalProfit = DB::table('salesorder')
            ->whereMonth('salesDate', $bulan)
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->sum('totalProfit');

        $profitYTD = DB::table('salesorder')
            ->select(DB::raw('MONTH(salesDate) as bulan'), DB::raw('SUM(totalProfit) as total'))
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        // Forecast
        $startNextMonth = Carbon::createFromDate($tahun, $bulan, 1)->addMonth()->startOfMonth();
        $endNextMonth   = Carbon::createFromDate($tahun, $bulan, 1)->addMonth()->endOfMonth();

        $forecastProducts = DB::table('sales_forecast as sf')
            ->join('product as p', 'sf.productID', '=', 'p.productID')
            ->select('p.productName', 'sf.forecast_quantity')
            ->whereBetween('sf.forecast_month', [$startNextMonth, $endNextMonth])
            ->where('p.status', 1)
            ->orderByDesc('sf.forecast_quantity')
            ->get();

        $shortageProducts = DB::table('sales_forecast as sf')
            ->join('product as p', 'sf.productID', '=', 'p.productID')
            ->select('p.productName', 'p.stock', 'sf.forecast_quantity')
            ->where('p.status', 1)
            ->whereBetween('sf.forecast_month', [$startNextMonth, $endNextMonth])
            ->whereColumn('sf.forecast_quantity', '>', 'p.stock')
            ->get();

        // Inventory
        $inventoryTopProducts = DB::table('salesdetail as sd')
            ->join('salesorder as so', 'sd.SalesOrder_salesID', '=', 'so.salesID')
            ->join('product as p', 'sd.Product_productID', '=', 'p.productID')
            ->select('p.productName', DB::raw('SUM(sd.quantity) as total_qty'))
            ->whereMonth('so.salesDate', $bulan)
            ->whereYear('so.salesDate', $tahun)
            ->where('so.status', 1)
            ->where('p.status', 1)
            ->groupBy('p.productName')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $inventoryTopMargin = DB::table('salesdetail as sd')
            ->join('salesorder as so', 'sd.SalesOrder_salesID', '=', 'so.salesID')
            ->join('product as p', 'sd.Product_productID', '=', 'p.productID')
            ->select(
                'p.productName',
                DB::raw('SUM(sd.subtotal - (sd.cost * sd.quantity)) as total_margin')
            )
            ->whereMonth('so.salesDate', $bulan)
            ->whereYear('so.salesDate', $tahun)
            ->where('so.status', 1)
            ->where('p.status', 1)
            ->groupBy('p.productName')
            ->orderByDesc('total_margin')
            ->limit(5)
            ->get();

        $lowStockProducts = DB::table('product')
            ->select('productName', 'stock', 'minStock')
            ->whereColumn('stock', '<=', DB::raw('minStock + 5')) // toleransi 5
            ->where('status', 1)
            ->orderBy('stock')
            ->get();

        // Return
        if ($user->role === 'Owner') {
            return view('Home.owner', [
                'title'                => 'Dashboard',
                'bulan'                => $bulan,
                'tahun'                => $tahun,
                'totalSales'           => $totalSales,
                'orderCount'           => $orderCount,
                'returned'             => $returned,
                'returnRate'           => $returnRate,
                'topProducts'          => $topProducts,
                'growth'               => $growth,
                'totalPurchase'        => $totalPurchase,
                'purchaseDetails'      => $purchaseDetails,
                'totalProfit'          => $totalProfit,
                'profitYTD'            => $profitYTD,
                'cashflow'             => $cashflow,
                'forecastProducts'     => $forecastProducts,
                'shortageProducts'     => $shortageProducts,
                'inventoryTopProducts' => $inventoryTopProducts,
                'inventoryTopMargin'   => $inventoryTopMargin,
                'lowStockProducts'     => $lowStockProducts,
            ]);
        }
        if ($user->role === 'Purchase') {
            return view('Home.purchase', [
                'title'                => 'Dashboard',
                'bulan'                => $bulan,
                'tahun'                => $tahun,
                'totalPurchase'        => $totalPurchase,
                'purchaseDetails'      => $purchaseDetails,
                'forecastProducts'     => $forecastProducts,
                'shortageProducts'     => $shortageProducts,
                'inventoryTopProducts' => $inventoryTopProducts,
                'inventoryTopMargin'   => $inventoryTopMargin,
                'lowStockProducts'     => $lowStockProducts,
            ]);
        }
        if ($user->role === 'Sales') {
            return view('Home.sales', [
                'title'                => 'Dashboard',
                'bulan'                => $bulan,
                'tahun'                => $tahun,
                'totalSales'           => $totalSales,
                'orderCount'           => $orderCount,
                'returned'             => $returned,
                'returnRate'           => $returnRate,
                'topProducts'          => $topProducts,
                'growth'               => $growth,
                'totalProfit'          => $totalProfit,

                'forecastProducts'     => $forecastProducts,
                'shortageProducts'     => $shortageProducts,
                'inventoryTopProducts' => $inventoryTopProducts,
                'inventoryTopMargin'   => $inventoryTopMargin,
                'lowStockProducts'     => $lowStockProducts,
            ]);

        }
        abort(403, 'Unauthorized.');
    }
}
