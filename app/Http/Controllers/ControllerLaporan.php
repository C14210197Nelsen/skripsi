<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ControllerLaporan extends Controller {
    public function index(Request $request) {
        $tanggal = $request->input('tanggal', date('Y-m'));
        [$tahun, $bulan] = explode('-', $tanggal);

        // 1. Total Penjualan
        $pendapatan = DB::table('salesorder')
            ->whereMonth('salesDate', $bulan)
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->sum('totalPrice');

        // 2. Total HPP dari SO
        $total_hpp = DB::table('salesorder')
            ->whereMonth('salesDate', $bulan)
            ->whereYear('salesDate', $tahun)
            ->where('status', 1)
            ->sum('totalHPP');

        // 3. Laba Kotor
        $laba_kotor = $pendapatan - $total_hpp;

        // 4. Total Pengeluaran dari Rekapan
        $pengeluaran = DB::table('rekapan')
            ->where('tipe', 'Pengeluaran')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');

        // 5. Pemasukan Tambahan (misal bunga, refund, dll.)
        $pemasukan_lain = DB::table('rekapan')
            ->where('tipe', 'Pemasukan')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');

        // 6. Laba Bersih
        $laba_bersih = $laba_kotor + $pemasukan_lain - $pengeluaran;

        // Grouped pemasukan berdasarkan kategori
        $pemasukan_per_kategori = DB::table('rekapan')
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->where('tipe', 'Pemasukan')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('kategori')
            ->get();

        // Grouped pengeluaran berdasarkan kategori
        $pengeluaran_per_kategori = DB::table('rekapan')
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->where('tipe', 'Pengeluaran')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('kategori')
            ->get();

        return view('laporan.index', [
            'title' => 'Laporan Laba Rugi',
            'user' => 'Nama Pengguna',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'pendapatan' => $pendapatan,
            'total_hpp' => $total_hpp,
            'laba_kotor' => $laba_kotor,
            'pengeluaran' => $pengeluaran,
            'pemasukan_lain' => $pemasukan_lain,
            'laba_bersih' => $laba_bersih,
            'pemasukan_per_kategori' => $pemasukan_per_kategori,
            'pengeluaran_per_kategori' => $pengeluaran_per_kategori
        ]);
    }

}
