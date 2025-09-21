@extends('layouts.main')

@section('title', 'Rekapan Bulanan')

@section('container')

<div class="container mt-4">
    <h4>Rekapan Bulanan (Pemasukan & Pengeluaran)</h4>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark text-center">
            <tr>
                <th>Periode</th>
                <th>Total Pemasukan</th>
                <th>Total Pengeluaran</th>
                <th>Selisih</th>
            </tr>
        </thead>
        <tbody class="text-center">
            @foreach($rekapanBulanan as $periode => $data)
                @php
                    $pemasukan = $data->firstWhere('tipe', 'pemasukan')->total ?? 0;
                    $pengeluaran = $data->firstWhere('tipe', 'pengeluaran')->total ?? 0;
                    $selisih = $pemasukan - $pengeluaran;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</td>
                    <td>Rp {{ number_format($pemasukan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($pengeluaran, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($selisih, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
