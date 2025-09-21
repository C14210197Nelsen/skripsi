<?php

namespace App\Http\Controllers;

use App\Models\Rekapan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ControllerRekapan extends Controller {
    public function index(Request $request) {
        $bulan = $request->input('tanggal') ?? now()->format('Y-m'); // format: YYYY-MM

        $year = substr($bulan, 0, 4);
        $month = substr($bulan, 5, 2);

        // Data Sales
        $salesData = DB::table('salesorder')
            ->whereYear('salesDate', $year)
            ->whereMonth('salesDate', $month)
            ->where('status', 1)
            ->get();

        $totalSales = $salesData->sum('totalPrice');

        // Data Purchase
        $purchaseData = DB::table('purchaseorder')
            ->whereYear('purchaseDate', $year)
            ->whereMonth('purchaseDate', $month)
            ->where('status', 1)
            ->get();

        $totalPurchase = $purchaseData->sum('totalPrice');

        // Rekapan manual dari tabel rekapan
        $rekapanManual = Rekapan::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        return view('rekapan.index', [
            'title' => 'Rekapan',
            'bulan' => $bulan,
            // 'user' => auth()->user()->name ?? 'Pengguna',
            'salesData' => $salesData,
            'purchaseData' => $purchaseData,
            'rekapanManual' => $rekapanManual,
            'totalSales' => $totalSales,
            'totalPurchase' => $totalPurchase,
        ]);
    }


    public function create() {
        $pemasukanKategori = [
            'Penjualan', 'Pendapatan Jasa', 'Piutang Dibayar',
            'Pendapatan Sewa', 'Pendapatan Bunga', 'Pemasukan Lainnya'
        ];

        $pengeluaranKategori = [
            'Gaji', 'Operasional', 'Pembelian Barang', 'Perawatan',
            'Transportasi', 'Listrik & Air', 'Sewa', 'Pajak',
            'Asuransi', 'Pengeluaran Lainnya'
        ];
        return view('rekapan.create',  [
            'title' => 'Rekapan',
            'user' => 'Nama Pengguna',
            'pemasukanKategori' => $pemasukanKategori,
            'pengeluaranKategori' => $pengeluaranKategori
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'tanggal'   => 'required|date',
            'kategori'  => 'required|string|max:64',
            'tipe'      => 'required|in:pemasukan,pengeluaran',
            'jumlah'    => 'required|integer|min:0',
            'metode'    => 'nullable|string|max:64',
            'deskripsi' => 'nullable|string|max:255',
        ]);


        Rekapan::create($validated);

        return redirect()->route('rekapan.index')->with('success', 'Rekapan berhasil ditambahkan.');
    }

    public function edit($rekapanID) {
        $rekapan = Rekapan::findOrFail($rekapanID);
        $pemasukanKategori = [
            'Penjualan', 'Pendapatan Jasa', 'Piutang Dibayar',
            'Pendapatan Sewa', 'Pendapatan Bunga', 'Pemasukan Lainnya'
        ];

        $pengeluaranKategori = [
            'Gaji', 'Operasional', 'Pembelian Barang', 'Perawatan',
            'Transportasi', 'Listrik & Air', 'Sewa', 'Pajak',
            'Asuransi', 'Pengeluaran Lainnya'
        ];
        return view('rekapan.edit',  [
            'title' => 'Rekapan',
            'user' => 'Nama Pengguna',
            'rekapan' => $rekapan,
            'pemasukanKategori' => $pemasukanKategori,
            'pengeluaranKategori' => $pengeluaranKategori
        ]);
    }

    public function update(Request $request, $rekapanID) {
        $request->validate([
            'tanggal'   => 'required|date',
            'kategori'  => 'required|string|max:64',
            'tipe'      => 'required|in:pemasukan,pengeluaran',
            'jumlah'    => 'required|integer|min:0',
            'metode'    => 'nullable|string|max:64',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $rekapan = \App\Models\Rekapan::findOrFail($rekapanID);
        $rekapan->update($request->all());

        return redirect()->route('rekapan.index')->with('success', 'Data berhasil diperbarui.');
    }


    public function destroy($rekapanID) {
        $rekapan = \App\Models\Rekapan::findOrFail($rekapanID);
        $rekapan->delete();

        return redirect()->route('rekapan.index')->with('success', 'Data berhasil dihapus.');
    }


}
