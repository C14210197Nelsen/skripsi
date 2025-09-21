@extends('layouts.main')

@section('title', 'Return Order')

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
    <h2 class="fw-semibold text-dark mb-0">Return Order</h2>
    <a href="{{ route('return.create') }}" class="btn btn-danger rounded-pill px-4">+ Create</a>
  </div>

  {{-- Filter --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="{{ route('return.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="type" class="form-select form-select-sm rounded-pill">
            <option value="">-- All Types --</option>
            <option value="sales" {{ request('type') == 'sales' ? 'selected' : '' }}>Sales Return</option>
            <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase Return</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
          @if(request()->hasAny(['type', 'partner_id']))
            <a href="{{ route('return.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- Table --}}
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">Doc No</th>
          <th style="width: 30%;">Partner</th>
          <th style="width: 10%;">Source</th>
          <th style="width: 20%;">Return Date</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($returnorders as $return)
          <tr>
            <td>{{ ($return->returnID) }}</td>
            <td>{{ $return->partner->name ?? '-' }}</td>
            <td>{{ $return->type === 'sales' ? 'SO' : 'PO' }} #{{ $return->sourceID }}</td>
            <td>{{ \Carbon\Carbon::parse($return->returnDate)->format('d-m-Y') }}</td>
            <td>Rp {{ number_format($return->total, 0, ',', '.') }}</td>
            <td>
              <a href="{{ route('return.show', $return->returnID) }}" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
              <form action="{{ route('return.destroy', $return->returnID) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus return ini?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-muted">No Return Order</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="circle-pagination mt-4">
    {{ $returnorders->links('pagination::bootstrap-5') }}
  </div>

</div>
@endsection
