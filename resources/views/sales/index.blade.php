@extends('layouts.main')

@section('title', 'Sales Order')

@section('container')
<div class="container mt-4">

  {{-- Notifikasi --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded">{{ session('success') }}</div>
  @endif
  @if($errors->has('from'))
    <div class="alert alert-danger shadow-sm rounded">{{ $errors->first('from') }}</div>
  @endif

  @if(session('warning'))
    <div class="alert alert-warning">{!! session('warning') !!}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger shadow-sm rounded">{{ session('error') }}</div>
  @endif


  {{-- Header dan Filter --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Sales Order</h2>

    {{-- Tombol Upload dan Create --}}
    <div class="d-flex gap-2">
      <a href="#" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">Excel</a>
      <a href="{{ route('sales.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>


  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('sales.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="customer_id" class="form-select form-select-sm rounded-pill">
            <option value="">-- All Customers --</option>
            @foreach($customers as $customer)
              <option value="{{ $customer->customerID }}" {{ request('customer_id') == $customer->customerID ? 'selected' : '' }}>
                {{ $customer->customerName }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <input type="month" name="from" class="form-control form-control-sm rounded-pill" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
          <input type="month" name="to" class="form-control form-control-sm rounded-pill" value="{{ request('to') }}">
        </div>
        <div class="col-md-1">
          <select name="delivered" class="form-select form-select-sm rounded-pill">
            <option value="">Delivered?</option>
            <option value="1" {{ request('delivered') === '1' ? 'selected' : '' }}>Delivered</option>
            <option value="0" {{ request('delivered') === '0' ? 'selected' : '' }}>Not Delivered</option>
          </select>
        </div>
        
        <div class="col-md-1">
          <select name="paid" class="form-select form-select-sm rounded-pill">
            <option value="">Paid?</option>
            <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Paid</option>
            <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Unpaid</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
          @if(request('customer_id') || request('from') || request('to') || request('delivered') !== null || request('paid') !== null)
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Tabel --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 10%;">Doc No</th>
          <th style="width: 20%;">Customer</th>
          <th style="width: 15%;">Date</th>
          <th style="width: 20%;">Status</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salesorders as $salesorder)
          <tr>
            <td>{{ ($salesorder->salesID) }}</td>
            <td>{{ $salesorder->customer->customerName ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($salesorder->salesDate)->format('d-m-Y') }}</td>
            <td>
              @if($salesorder->isDelivered)
                <span class="badge bg-success">Delivered</span>
              @else
                <span class="badge bg-warning">Not Delivered</span>
              @endif

              @if($salesorder->isPaid)
                <span class="badge bg-success">Paid</span>
              @else
                <span class="badge bg-danger">Unpaid</span>
              @endif
            </td>
            <td>Rp {{ number_format($salesorder->totalPrice, 0, ',', '.') }}</td>
            <td>
              <a href="{{ route('sales.show', $salesorder->salesID) }}" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
              <a href="{{ route('sales.edit', $salesorder->salesID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
              <form action="{{ route('sales.destroy', $salesorder->salesID) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sales order ini?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-muted">No Sales Order</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="circle-pagination mt-4">
    {{ $salesorders->links('pagination::bootstrap-5') }}
  </div>

</div>

<!-- Modal Upload Excel -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('sales.import.submit') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Sales Order (Excel)</h5>
        </div>
        <div class="modal-body">
          <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </div>
    </form>
  </div>
</div>


@endsection
