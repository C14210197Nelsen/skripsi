@extends('layouts.main')

@section('title', 'Rekapan Bulanan')

@section('container')
<div class="container">

  {{-- Alert --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded">{{ session('success') }}</div>
  @endif

  {{-- Filter --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('rekapan.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
              <label class="form-label mb-1">Filter Bulan</label>
              <input type="text" name="tanggal" 
                  class="form-control form-control-sm rounded-pill monthpicker"
                  value="{{ request('tanggal', date('Y-m')) }}">
        </div>

        <div class="col-md-5 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Go</button>
          @if(request()->has('tanggal'))
            <a href="{{ route('rekapan.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
          @endif
        </div>

        <div class="col-md-3 text-end">
          <a href="{{ route('rekapan.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
        </div>
      </form>
    </div>
  </div>


  <div class="row g-3">
    <div class="col-md-6">
      {{-- Tabel Pemasukan --}}
      <h5 class="fw-bold text-success">Pemasukan</h5>
      <table class="table table-bordered text-center align-middle">
        <thead class="table-success">
          <tr>
            <th width="20%">Tanggal</th>
            <th width="40%">Keterangan</th>
            <th width="20%">Total</th>
            <th width="20%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr data-bs-toggle="collapse" data-bs-target="#salesDetail" class="cursor-pointer">
            <td>{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}</td>
            <td class="text-start">Pemasukan dari Penjualan</td>
            <td>Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            <td><span class="text-primary">Lihat Detail</span></td>
          </tr>
          <tr>
            <td colspan="4" class="p-0">
              <div class="collapse" id="salesDetail">
                <table class="table table-sm table-striped table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Sales ID</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($salesData as $s)
                      <tr>
                        <td>{{ \Carbon\Carbon::parse($s->salesDate)->format('d-m-Y') }}</td>
                        <td>Sales #{{ $s->salesID }}</td>
                        <td>Rp {{ number_format($s->totalPrice, 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          {{-- Manual pemasukan --}}
          @foreach ($rekapanManual->where('tipe', 'pemasukan') as $r)
          <tr>
            <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
            <td class="text-start">{{ $r->deskripsi }}</td>
            <td>Rp {{ number_format($r->jumlah, 0, ',', '.') }}</td>
            <td class="text-nowrap">
              <a href="{{ route('rekapan.edit', $r->rekapanID) }}"
                class="btn btn-sm btn-outline-warning btn-icon" title="Edit">
                ✏️
              </a>

              <form action="{{ route('rekapan.destroy', $r->rekapanID) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"
                        onclick="return confirm('Hapus data ini?')">
                  🗑️
                </button>
              </form>
            </td>

          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="col-md-6">
      {{-- Tabel Pengeluaran --}}
      <h5 class="fw-bold text-danger">Pengeluaran</h5>
      <table class="table table-bordered text-center align-middle">
        <thead class="table-danger">
          <tr>
            <th width="20%">Tanggal</th>
            <th width="40%">Keterangan</th>
            <th width="20%">Total</th>
            <th width="20%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr data-bs-toggle="collapse" data-bs-target="#purchaseDetail" class="cursor-pointer">
            <td>{{ \Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y') }}</td>
            <td class="text-start">Pengeluaran dari Pembelian</td>
            <td>Rp {{ number_format($totalPurchase, 0, ',', '.') }}</td>
            <td><span class="text-primary">Lihat Detail</span></td>
          </tr>
          <tr>
            <td colspan="4" class="p-0">
              <div class="collapse" id="purchaseDetail">
                <table class="table table-sm table-striped table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Purchase ID</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($purchaseData as $s)
                      <tr>
                        <td>{{ \Carbon\Carbon::parse($s->purchaseDate)->format('d-m-Y') }}</td>
                        <td>Purchase #{{ $s->purchaseID }}</td>
                        <td>Rp {{ number_format($s->totalPrice, 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          {{-- Manual Pengeluaran --}}
          @foreach ($rekapanManual->where('tipe', 'pengeluaran') as $r)
          <tr>
            <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
            <td class="text-start">{{ $r->deskripsi }}</td>
            <td>Rp {{ number_format($r->jumlah, 0, ',', '.') }}</td>
            <td class="text-nowrap">
              <a href="{{ route('rekapan.edit', $r->rekapanID) }}"
                class="btn btn-sm btn-outline-warning btn-icon" title="Edit">
                ✏️
              </a>

              <form action="{{ route('rekapan.destroy', $r->rekapanID) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"
                        onclick="return confirm('Hapus data ini?')">
                  🗑️
                </button>
              </form>
            </td>

          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  
  {{-- Summary
  <div class="card bg-light p-3 shadow-sm">
    <h5 class="mb-2 fw-bold">Total Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</h5>
    <ul class="list-unstyled">
      <li><strong>Total Pemasukan:</strong> Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</li>
      <li><strong>Total Pengeluaran:</strong> Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</li>
      <li><strong>Saldo Akhir:</strong> Rp {{ number_format($saldo, 0, ',', '.') }}</li>
    </ul>
  </div> --}}

</div>

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
