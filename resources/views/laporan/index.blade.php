@extends('layouts.main')

@section('title', 'Laporan Laba Rugi')

@section('container')
<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-4">
                <label class="form-label mb-1">Filter Bulan</label>
                <input type="text" name="tanggal" 
                    class="form-control form-control-sm rounded-pill monthpicker"
                    value="{{ request('tanggal', date('Y-m')) }}">

        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Go</button>
        </div>
    </div>
</form>


<h4><strong>Laporan Laba Rugi Bulan {{ $bulan }}/{{ $tahun }}</strong></h4>

<table class="table table-bordered">
    <tr><th class="text-start">Pendapatan</th><td class="text-success">Rp{{ number_format($pendapatan) }}</td></tr>
    <tr><th class="text-start">Harga Pokok Penjualan</th><td class="text-danger">- Rp{{ number_format($total_hpp) }}</td></tr>
    <tr class="table-warning"><th><strong>Laba Kotor</strong></th><td><strong>Rp{{ number_format($laba_kotor) }}</strong></td></tr>
    <tr><th class="text-start">Pemasukan Lain</th><td class="text-success">Rp{{ number_format($pemasukan_lain) }}</td></tr>
    <tr><td colspan="2" class="text-start">
        <ul class="mb-2">
            @foreach ($pemasukan_per_kategori as $item)
                <li>{{ $item->kategori }}: Rp{{ number_format($item->total) }}</li>
            @endforeach
        </ul>
    </td></tr>

    <tr><th class="text-start">Pengeluaran</th><td class="text-danger">- Rp{{ number_format($pengeluaran) }}</td></tr>
    <tr><td colspan="2" class="text-start">
        <ul class="mb-2">
            @foreach ($pengeluaran_per_kategori as $item)
                <li>{{ $item->kategori }}: Rp{{ number_format($item->total) }}</li>
            @endforeach
        </ul>
    </td></tr>

    <tr class="table-success">
        <th><strong>Laba Bersih</strong></th><td><strong>Rp{{ number_format($laba_bersih) }}</strong></td>
    </tr>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<script>
$(function() {
    $('.monthpicker').datepicker({
        format: "yyyy-mm",     
        startView: "months",   
        minViewMode: "months",  
        autoclose: true,      
        todayHighlight: true
    });
});
</script>

@endsection
