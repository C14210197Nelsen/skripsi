@extends('layouts.main')

@section('container')
<div class="container-fluid">
    <h3 class="mb-4">Dashboard</h3>

    {{-- Filter --}}
    <form method="GET" action="{{ route('home') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="bulan" class="form-select form-select-sm">
                @foreach (range(1, 12) as $b)
                    <option value="{{ sprintf('%02d', $b) }}" {{ $bulan == sprintf('%02d', $b) ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $b)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="tahun" class="form-select form-select-sm">
                @for ($t = date('Y'); $t >= date('Y') - 5; $t--)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-danger btn-sm rounded-pill">Filter</button>
        </div>
    </form>

    {{-- Purchase --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-warning text-dark"><i class="bi bi-cart4"></i> Total Pembelian</div>
                <div class="card-body"><h3 class="fw-bold">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</h3></div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-warning text-dark"><i class="bi bi-box-seam"></i> Detail Pembelian</div>
                <div class="card-body">
                    @if($purchaseDetails->isEmpty())
                        <p class="text-muted">Belum ada pembelian bulan ini.</p>
                    @else
                        <div style="max-height:300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light"><tr><th>Produk</th><th>Qty</th><th>Total (Rp)</th></tr></thead>
                                <tbody>
                                    @foreach($purchaseDetails as $pd)
                                        <tr>
                                            <td>{{ $pd->productName }}</td>
                                            <td>{{ $pd->total_qty }}</td>
                                            <td>Rp {{ number_format($pd->total_value, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Forecasting --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card border-light shadow-sm mb-3">
                <div class="card-header bg-info text-dark">
                    <i class="bi bi-graph-up-arrow"></i> Prediksi Penjualan Bulan Depan (Top Produk)
                </div>
                <div class="card-body">
                    @if($forecastProducts->isEmpty())
                        <p class="text-muted">Belum ada data forecast untuk bulan depan.</p>
                    @else
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-center">Forecast Qty</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($forecastProducts as $fp)
                                    <tr>
                                        <td class="text-start">{{ $fp->productName }}</td>
                                        <td class="text-center">{{ $fp->forecast_quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-info text-dark"><i class="bi bi-exclamation-triangle"></i> Produk Berisiko Shortage</div>
                <div class="card-body">
                    @if($shortageProducts->isEmpty())
                        <p class="text-muted">Tidak ada produk berisiko shortage bulan depan.</p>
                    @else
                        <div style="max-height:300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light"><tr><th>Produk</th><th>Stok</th><th>Forecast</th></tr></thead>
                                <tbody>
                                    @foreach($shortageProducts as $sp)
                                        <tr>
                                            <td>{{ $sp->productName }}</td>
                                            <td>{{ $sp->stock }}</td>
                                            <td>{{ $sp->forecast_quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Inventory --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-danger text-light">
                    <i class="bi bi-graph-up"></i> Margin Terbesar
                </div>
                <div class="card-body">
                    @if($inventoryTopMargin->isEmpty())
                        <p class="text-muted">Belum ada penjualan bulan ini.</p>
                    @else
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-end">Total Margin</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @foreach($inventoryTopMargin as $itm)
                                    <tr>
                                        <td class="text-start">{{ $itm->productName }}</td>
                                        <td class="text-end">Rp {{ number_format($itm->total_margin, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-danger text-light"><i class="bi bi-exclamation-circle"></i> Stok Hampir Habis</div>
                <div class="card-body">
                    @if($lowStockProducts->isEmpty())
                        <p class="text-muted">Tidak ada produk dengan stok rendah.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($lowStockProducts as $ls)
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>{{ $ls->productName }}</span>
                                    <span>{{ $ls->stock }} / {{ $ls->minStock }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("toggleTopProducts");
        if (btn) {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".extra-product").forEach(el => el.classList.toggle("d-none"));
                btn.textContent = btn.textContent.includes("10") ? "Tampilkan Top 5" : "Tampilkan Top 10";
            });
        }
    });

</script>
@endsection
