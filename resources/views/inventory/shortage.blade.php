@extends('layouts.main')

@section('title', 'Shortage Products')

@section('container')
<div class="container mt-4">
  <h2 class="fw-semibold text-dark mb-4">Shortage Products (Forecast vs Stock)</h2>

  <form action="{{ route('purchase.create') }}" method="GET">
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle text-center shadow-sm">
        <thead class="table-dark text-white">
          <tr>
            <th><input type="checkbox" id="checkAll"></th>
            <th>Code</th>
            <th>Product</th>
            <th>Stock</th>
            <th>Forecast Qty</th>
            <th>Shortage</th>
          </tr>
        </thead>
        <tbody>
            @forelse($shortageProducts as $product)
                <tr>
                <td>
                    <input type="checkbox" name="products[]" value="{{ $product->productID }}">
                    <input type="hidden" name="shortage[{{ $product->productID }}]" 
                        value="{{ $product->forecast_quantity - $product->stock }}">
                </td>
                <td>{{ $product->productCode }}</td>
                <td>{{ $product->productName }}</td>
                <td>{{ $product->stock }}</td>
                <td>{{ $product->forecast_quantity }}</td>
                <td class="{{ ($product->forecast_quantity - $product->stock) > 50 ? 'bg-danger text-white' : 'text-danger fw-bold' }}">
                    {{ $product->forecast_quantity - $product->stock }}
                </td>
                </tr>
            @empty
                <tr>
                <td colspan="6" class="text-muted">No Shortage Products</td>
                </tr>
            @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3 d-flex justify-content-end gap-2">
        <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back</a>
    <button type="submit" class="btn btn-danger rounded-pill px-4">Create PO</button>
    </div>
  </form>
</div>

<script>
  // Checkbox check all
  document.getElementById('checkAll').addEventListener('change', function() {
    document.querySelectorAll('input[type=checkbox][name^="products"]').forEach(cb => cb.checked = this.checked);
  });

  document.querySelector('form').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('input[type=checkbox][name^="products"]:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert("Please select at least one product.");
    }
    });
</script>
@endsection
