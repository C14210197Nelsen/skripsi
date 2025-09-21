@extends('layouts.main')

@section('title', 'Purchase Order')

@section('container')
<div class="container mt-4">

  {{-- Alert --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded">{{ session('success') }}</div>
  @endif
  @if($errors->has('from'))
    <div class="alert alert-danger shadow-sm rounded">{{ $errors->first('from') }}</div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Purchase Order</h2>
    <a href="{{ route('purchase.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
  </div>

  {{-- Filter --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('purchase.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="supplier_id" class="form-select form-select-sm rounded-pill">
            <option value="">-- All Suppliers --</option>
            @foreach($suppliers as $supplier)
              <option value="{{ $supplier->supplierID }}" {{ request('supplier_id') == $supplier->supplierID ? 'selected' : '' }}>
                {{ $supplier->supplierName }}
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
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
          @if(request('supplier_id') || request('from') || request('to'))
            <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
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
          <th style="width: 5%;">Doc No</th>
          <th style="width: 35%;">Supplier</th>
          <th style="width: 20%;">Date</th>
          <th style="width: 20%;">Total</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($purchaseorders as $purchaseorder)
        <tr>
          <td>{{ ($purchaseorder->purchaseID) }}</td>
          <td>{{ $purchaseorder->supplier->supplierName ?? '-' }}</td>
          <td>{{ \Carbon\Carbon::parse($purchaseorder->purchaseDate)->format('d-m-Y') }}</td>
          <td>Rp {{ number_format($purchaseorder->totalPrice, 0, ',', '.') }}</td>
          <td>
            <a href="{{ route('purchase.show', $purchaseorder->purchaseID) }}" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
            <a href="{{ route('purchase.edit', $purchaseorder->purchaseID) }}" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
            <form action="{{ route('purchase.destroy', $purchaseorder->purchaseID) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus purchase order ini?');">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="text-muted">No Purchase Order</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="circle-pagination mt-4">
    {{ $purchaseorders->links('pagination::bootstrap-5') }}
  </div>

</div>
@endsection
