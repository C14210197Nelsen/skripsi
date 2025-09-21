@extends('layouts.main')

@section('title', 'Create Return Order')

@section('container')
<div class="container mt-4">

  {{-- Alert --}}
  @if(session('error'))
    <div class="alert alert-danger shadow-sm rounded">{{ session('error') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger shadow-sm rounded">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-dark">Return Order</h2>
    <span class="badge bg-danger fs-6">{{ $source_prefix }} #{{ $source_id }}</span>
  </div>

  <form action="{{ route('return.store') }}" method="POST" class="card shadow-sm p-4">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="source_id" value="{{ $source_id }}">

    {{-- Partner --}}
    <div class="mb-3">
      <label class="form-label fw-semibold">Partner</label>
      <input type="text" class="form-control-plaintext fw-medium" value="{{ $partner_name }}" readonly>
    </div>

    {{-- Return Date --}}
    <div class="mb-3">
      <label class="form-label fw-semibold">Return Date</label>
      <input type="date" name="returnDate" class="form-control rounded-pill" value="{{ date('Y-m-d') }}" required>
    </div>

    {{-- Produk --}}
    <h5 class="mt-4 fw-semibold text-black">Products to Return</h5>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-hover align-middle text-center shadow-sm">
        <thead class="table-dark text-white">
          <tr>
            <th style="width: 5%;">No</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Qty Return</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $index => $item)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>
              {{ $item->product_name }}
              <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
            </td>
            <td>
              {{ $item->quantity }} - {{ $item->returned }} = <strong>{{ $item->quantity - $item->returned }}</strong>
            </td>
            <td>
              <input type="number"
                     name="items[{{ $index }}][qty_return]"
                     class="form-control text-center"
                     min="0"
                     max="{{ $item->quantity - $item->returned }}"
                     value="{{ old('items.' . $index . '.qty_return', 0) }}">
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Tombol --}}
    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="{{ route('return.create') }}" class="btn btn-outline-danger rounded-pill">Discard</a>
      <button type="submit" class="btn btn-danger rounded-pill px-4">Submit Return</button>
    </div>

  </form>
</div>
@endsection

@section('script')
<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const inputs = document.querySelectorAll('input[name^="items"]');
  for (const input of inputs) {
    const max = parseInt(input.max);
    const value = parseInt(input.value);
    if (value < 0) {
      alert('Qty Return tidak boleh negatif.');
      e.preventDefault();
      return false;
    }
    if (value > max) {
      alert('Qty Return tidak boleh lebih dari stok yang tersedia.');
      e.preventDefault();
      return false;
    }
  }
});
</script>
@endsection

