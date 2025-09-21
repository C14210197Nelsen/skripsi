@extends('layouts.main')

@section('title', 'Inventory')

@section('container')
{{-- Notifikasi Stok Minimum --}}
@php
  $lowStockProducts = $products->filter(fn($product) => $product->getStock() <= $product->minStock);
@endphp

@if ($lowStockProducts->count() > 0)
  <div class="alert alert-warning shadow-sm rounded-3">
    <h6 class="mb-2 fw-semibold">⚠️ Produk dengan stok rendah:</h6>
    <ul class="mb-0 ps-3">
      @foreach ($lowStockProducts as $product)
        <li>
          <span class="{{ $product->getStock() <= 0 ? 'text-danger fw-bold' : '' }}">
            {{ $product->productName }} 
            (Stok: {{ $product->getStock() }} | Min: {{ $product->minStock }})
          </span>
        </li>
      @endforeach
    </ul>
  </div>
@endif

<div class="container mt-4">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Inventory</h2>
    <div class="d-flex gap-2">
      <a href="{{ route('inventory.deleted') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Deleted</a>
      <a href="{{ route('inventory.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>

  {{-- Tabel --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 8%;">Kode Produk</th>
          <th style="width: 40%;">Nama Produk / Varian</th>
          <th style="width: 7%;">Harga</th>
          <th style="width: 7%;">HPP</th>
          <th style="width: 3%;">Stok</th>
          <th style="width: 3%;">Min</th>
          <th style="width: 25%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($products as $product)
        <tr @if($product->getStock() <= $product->minStock) class="table-warning" @endif>
          <td style="white-space: normal; word-wrap: break-word; max-width: 100px;">{{ $product->productCode }}</td>
          <td style="white-space: normal; word-wrap: break-word; max-width: 700px;" >{{ $product->productName }}</td>
          <td>Rp {{ number_format($product->productPrice, 0, ',', '.') }}</td>
          <td>Rp {{ number_format($product->getHPP(), 0, ',', '.') }}</td>
          <td>{{ $product->getStock() }}</td>
          <td>{{ $product->minStock }}</td>
          <td class="justify-content-center gap-1 flex-wrap">
            <a href="{{ route('stockledger.show', $product->productID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Movement</a>

            <button 
                class="btn btn-sm btn-outline-info rounded-pill forecast-btn" 
                data-product-id="{{ $product->productID }}">
                Forecast
            </button>


            <a href="{{ route('inventory.edit', $product->productID) }}" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
            <form action="{{ route('inventory.destroy', $product->productID) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>


{{-- Modal Forecast --}}
<div class="modal fade" id="forecastModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Forecast Produk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- Loading --}}
        <div id="forecast-loading" class="text-center my-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p>Processing...</p>
          <span id="forecast-status-text"></span>
        </div>

        <div id="forecastMessage"></div>


        {{-- Forecast --}}
        <div id="forecast-content" style="display:none;">
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>Model Terbaik:</strong> <span id="forecast-model"></span><br>
              <strong>MAE:</strong> <span id="forecast-mae"></span>
            </div>
            <div class="col-md-6 text-end">
              <button id="btn-change-model" type="button" class="btn btn-warning btn-sm me-2">Change Model</button>
              <button id="btn-run-forecast" type="button" class="btn btn-success btn-sm">Run Forecast</button>
            </div>
          </div>

          {{-- Chart --}}
          <canvas id="forecastChart" height="150"></canvas>

          {{-- Tabel --}}
          <table class="table table-sm table-bordered mt-3">
            <thead>
              <tr>
                <th>Bulan</th>
                <th>Quantity Forecast</th>
              </tr>
            </thead>
            <tbody id="forecast-table">
              {{-- JS --}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chartInstance = null; 
$(document).ready(function() {
  // Tombol forecast diklik
  $('.forecast-btn').click(function() {
    const productID = $(this).data('product-id');

    $('#forecastModal').data('product-id', productID);

    $('#forecastModal').modal('show');
    $('#forecast-loading').show();
    $('#forecast-content').hide();

    // Ambil forecast terakhir via AJAX
    $.get(`/forecast/${productID}`, function(res) {
      if(res.status === 'success') {
        if (res.isRunning) {
          // Masih training → tampilkan loading dan status saja
          $('#forecastMessage').html(
            `<div class="alert alert-info">${res.message}</div>`
          );
          $('#forecast-loading').show();
          $('#forecast-content').hide();
          return; // stop supaya tidak render chart kosong
        }


        // Pesan kalau ada
        if (res.message) {
          $('#forecastMessage').html(
            `<div class="alert alert-warning">${res.message}</div>`
          );
        } else {
          $('#forecastMessage').empty(); // hapus pesan kalau sudah ada model
        }

        // Tampilkan model & MAE
        $('#forecast-model').text(`p=${res.model.p}, d=${res.model.d}, q=${res.model.q}`);
        $('#forecast-mae').text(res.model.mae);

        // Tampilkan tabel
        const tbody = $('#forecast-table');
        tbody.empty();
        
        res.forecast_next_2_months
            .slice()
            .reverse()
            .forEach(f => {
                // Hanya ambil tahun-bulan
                // '2025-09-01' → '2025-09'
                const ym = f.month.slice(0,7); 
                tbody.append(`<tr><td>${ym}</td><td>${f.qty}</td></tr>`);
            });


        // Buat chart
        const labels = res.chart.labels.map(l => l.slice(0,7));
        const actual = res.chart.actual.map(v => v);
        const forecast = res.chart.forecast.map(v => v);

        // const actual = res.chart.actual.map(v => v === 0 ? null : v);   // ubah 0 jadi null
        // const forecast = res.chart.forecast.map(v => (v === 0 ? null : v)); // ubah 0 jadi null


        if(chartInstance) chartInstance.destroy();
        const ctx = document.getElementById('forecastChart').getContext('2d');
        chartInstance = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              { label: 'Actual', data: actual, borderColor:'black', tension:0.2 },
              { label: 'Forecast', data: forecast, borderColor:'red', borderDash:[5,5], tension:0.2 },
            ]
          },
          options: { responsive:true, plugins:{legend:{position:'top'}} }
        });

        $('#forecast-loading').hide();
        $('#forecast-content').show();
      } else {
        alert(res.message);
        $('#forecastModal').modal('hide');
      }
    });
  });

  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  // Tombol Change Model
  $('#btn-change-model').click(function() {
    const productID = $('#forecastModal').data('product-id');
    $('#forecast-loading').show();
    $('#forecast-content').hide();

    // Trigger async job
    $.post(`forecast/train_model/${productID}`, function(res) {
        if(res.status === 'Queued') {
            const jobId = res.job_id;
            checkForecastJobStatus(jobId);
        } else {
            alert(res.message);
        }
    });


    //$.post(`/train_model/${productID}`, function(res) {
      //if(res.status === 'success') {
        // Panggil ulang endpoint forecast untuk update modal
        //$.get(`/forecast/${productID}`, function(data) {
          // update modal dengan data baru
         //updateForecastModal(data);
        //});
     // } else {
       // alert(res.message);
     // }
    //});
  });

  // Tombol Run Forecast
  $('#btn-run-forecast').click(function() {
    const productID = $('#forecastModal').data('product-id');
    $('#forecast-loading').show();
    $('#forecast-content').hide();

    $.post(`forecast/run_forecast/${productID}`, function(res) {
      if(res.status === 'success') {
        $.get(`/forecast/${productID}`, function(data) {
          updateForecastModal(data);
        });
      } else {
        alert(res.message);
      }
    });
  });
});

function updateForecastModal(res) {
  if(res.status === 'success') {

    if (res.message) {
      $('#forecastMessage').html(
        `<div class="alert alert-warning">${res.message}</div>`
      );
    } else {
      $('#forecastMessage').empty();
    }

    $('#forecast-model').text(`p=${res.model.p}, d=${res.model.d}, q=${res.model.q}`);
    $('#forecast-mae').text(res.model.mae);

    const tbody = $('#forecast-table');
    tbody.empty();
    res.forecast_next_2_months.slice().reverse().forEach(f => {
      const ym = f.month.slice(0,7);
      tbody.append(`<tr><td>${ym}</td><td>${f.qty}</td></tr>`);
    });

    // Destroy chart lama jika ada
    if(chartInstance !== null) {
      chartInstance.destroy();
    }

    const ctx = document.getElementById('forecastChart').getContext('2d');
    chartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: res.chart.labels,
        datasets: [
          { label: 'Actual', data: res.chart.actual, borderColor:'black', tension:0.2 },
          { label: 'Forecast', data: res.chart.forecast, borderColor:'red', borderDash:[5,5], tension:0.2 },
        ]
      },
      options: { responsive:true, plugins:{legend:{position:'top'}} }
    });

    $('#forecast-loading').hide();
    $('#forecast-content').show();
  } else {
    alert(res.message);
    $('#forecastModal').modal('hide');
  }
}

// function checkForecastJobStatus(jobId) {
//     $.get(`/forecast/status/${jobId}`, function(res) {
//         $('#forecast-status-text').text(`Training Status: ${res.status}`);

//         if (res.status === 'Done') {
//             // Ambil forecast terakhir setelah job selesai
//             const productID = $('#forecastModal').data('product-id');
//             $.get(`/forecast/${productID}`, function(data) {
//                 // Validasi hasil di dalam payload
//                 if (data.status === "error") {
//                     $('#forecast-status-text').text(`Training Failed: ${data.message}`);
//                     $('#forecast-loading').hide(); // sembunyikan spinner
//                     $('#forecast-chart').hide();   // sembunyikan chart
//                 } else {
//                     updateForecastModal(data); // tampilkan chart
//                 }
//             });

//         } else if (res.status === 'Pending' || res.status === 'Running') {
//             setTimeout(() => checkForecastJobStatus(jobId), 2000);

//         } else if (res.status === 'Error') {
//             alert(res.message);
//             $('#forecastModal').modal('hide');
//         }
//     });
// }

function checkForecastJobStatus(jobId) {

  
  $.get(`/forecast/status/${jobId}`, function(res) {
    $('#forecast-status-text').text(`Training Status: ${res.status}`);

    if (res.status === 'Done') {
      // Jika job selesai DAN payload menyatakan error → tampilkan error & stop
      if (res.hasError && res.result && res.result.message) {
        // $('#forecastMessage').html(
        //   `<div class="alert alert-danger">${res.result.message}</div>`
        // );
        alert(res.result.message);
        $('#forecast-loading').hide();
        $('#forecast-content').hide();
        
        if (window.chartInstance) { try { chartInstance.destroy(); } catch(e){} }
        return; 
      }

      // Ambil data forecast terbaru (jika sukses)
      const productID = $('#forecastModal').data('product-id');
      $.get(`/forecast/${productID}`, function(data) {
        updateForecastModal(data);
      });

    } else if (res.status === 'Pending' || res.status === 'Running') {
      setTimeout(() => checkForecastJobStatus(jobId), 2000);

    } else if (res.status === 'Error') {
      // Status job Error (bukan Done)
      const msg = (res.hasResult && res.result && res.result.message)
        ? res.result.message
        : (res.message || 'Terjadi kesalahan saat training.');

      $('#forecastMessage').html(`<div class="alert alert-danger">${msg}</div>`);
      $('#forecast-loading').hide();
      $('#forecast-content').hide();

    } else if (res.status === 'not_found') {
      $('#forecastMessage').html(
        `<div class="alert alert-warning">Job tidak ditemukan.</div>`
      );
      $('#forecast-loading').hide();
      $('#forecast-content').hide();
    }
  });
}




</script>


@endsection
