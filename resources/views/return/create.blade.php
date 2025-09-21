@extends('layouts.main')

@section('title', 'Return Order')

@section('container')
<div class="container mt-4">

  {{-- Alert --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded">{{ session('success') }}</div>
  @endif

  {{-- Filter Section --}}
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="btn-group">
      <a href="{{ route('return.create') }}" class="btn btn-sm {{ request('type') == '' ? 'btn-danger' : 'btn-outline-danger' }}">All</a>
      <a href="{{ route('return.create', ['type' => 'sales']) }}" class="btn btn-sm {{ request('type') == 'sales' ? 'btn-danger' : 'btn-outline-danger' }}">Sales Order</a>
      <a href="{{ route('return.create', ['type' => 'purchase']) }}" class="btn btn-sm {{ request('type') == 'purchase' ? 'btn-danger' : 'btn-outline-danger' }}">Purchase Order</a>
    </div>

    <form action="{{ route('return.create') }}" method="GET" class="row g-2 align-items-center">
      @if(request('type'))
        <input type="hidden" name="type" value="{{ request('type') }}">
      @endif

      <div class="col-md-auto">
        <select name="partner_id" class="form-select form-select-sm rounded-pill" style="min-width: 200px;">
          <option value="">-- All Customers/Suppliers --</option>
          @foreach($partners as $partner)
            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
              {{ $partner->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
        @if(request()->hasAny(['type', 'partner_id']))
          <a href="{{ route('return.create') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
        @endif
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th style="width: 10%;">Source</th>
          <th style="width: 30%;">Partner</th>
          <th style="width: 10%;">Type</th>
          <th style="width: 20%;">Date</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 10%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td>{{ ($orders->firstItem() ?? 0) + $loop->index }}</td>
            <td>{{ $order->type === 'sales' ? 'SO' : 'PO' }} #{{ $order->sourceID }}</td>
            <td>{{ $order->partner_name ?? '-' }}</td>
            <td>{{ ucfirst($order->type) }}</td>
            <td>{{ \Carbon\Carbon::parse($order->date)->format('d-m-Y') }}</td>
            <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            <td>
              <a href="{{ route('return.form', ['type' => $order->type, 'id' => $order->sourceID]) }}"
                 class="btn btn-sm btn-outline-danger rounded-pill">Return</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted">No Active Order</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="circle-pagination mt-4">
    {{ $orders->links('pagination::bootstrap-5') }}
  </div>
    <div class="d-flex justify-content-end mt-4">
    <a href="{{ route('return.index') }}" class="btn btn-outline-danger rounded-pill px-4">
      ← Back to Return
    </a>
  </div>

</div>
@endsection
