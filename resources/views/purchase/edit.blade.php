@extends('layouts.main')

@section('container')
<div class="container mt-4">
  <h2>Purchase Order (Edit)</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
              @endforeach
          </ul>
      </div>
  @endif
    
  <form action="{{ route('purchase.update', $purchaseorder->purchaseID) }}" method="POST">
    @csrf
@method('PUT')

    {{-- Supplier --}}
    <div class="mb-3">
      <label for="supplier" class="form-label">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-select" required>
          <option value="">-- Choose supplier --</option>
          @foreach($suppliers as $supplier)
            <option value="{{ $supplier->supplierID }}"
              {{ old('supplier_id', $purchaseorder->Supplier_supplierID) == $supplier->supplierID ? 'selected' : '' }}>
              {{ $supplier->supplierName }}{{ $supplier->status == 0 ? ' (Nonactive)' : '' }}
            </option>
          @endforeach
        </select>

    </div>

    <div class="mb-3">
      <label for="salesDate" class="form-label">Date</label>
      <input type="date" name="purchaseDate" id="purcaseDate" class="form-control" value="{{ old('purchaseDate', \Carbon\Carbon::parse($purchaseorder->purchaseDate)->format('Y-m-d')) }}">
    </div>


    <hr class="my-4">
    <h5 class="text-black">Product</h5>


    @php
      $oldProducts = old('products', $purchaseorder->purchasedetails->map(function($d) {
          return [
              'productCode' => $d->product->productCode,
              'quantity' => $d->quantity,
              'returned' => $d->returned,
              'cost' => $d->price,
              'productName' => $d->product->productName
          ];
      })->toArray());
    @endphp 

    <div id="product-list">
      @foreach($oldProducts as $item)
      @php $index = $loop->index; @endphp 
      <div class="row mb-2 product-item">
        <div class="col-md-3">
          <input list="productCodes" name="products[{{ $index }}][productCode]" class="form-control product-code"
            placeholder="Product Code" value="{{ $item['productCode'] ?? '' }}" required readonly>
        </div>
        <div class="col-md-2">
          <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity"
            placeholder="Qty" value="{{ ($item['quantity'] ?? 0) - ($item['returned'] ?? 0) }}" min="0" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="products[{{ $index }}][cost]" class="form-control cost"
            placeholder="Price" value="{{ $item['cost'] ?? '' }}" min="0" required readonly>
        </div>
        <div class="col-md-2">
          <input type="text" class="form-control subtotal" placeholder="Subtotal" disabled>
        </div>

        <p>
        
        <div class="col-md-10 small">
          <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" value="{{ $item['productName'] ?? '' }}" readonly>
        </div>
      </div>
      @endforeach
    </div>

    <datalist id="productCodes">
      @foreach($products as $product)
        <option value="{{ $product->productCode }}">{{ $product->productName }}</option>
      @endforeach
    </datalist>

    <br>
      <button type="button" id="add-product" class="btn btn-outline-secondary text-black mb-3">+ Add Product</button>
    </br>

    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('purchase.index') }}" class="btn btn-outline-danger">← Back</a>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>


  </form>
</div>
@endsection

@section('scripts')
<script>
let productIndex = document.querySelectorAll('.product-item').length;


// Tambah produk baru
document.getElementById('add-product').addEventListener('click', function () {
  const container = document.createElement('div');
  container.className = 'row mb-2 product-item';

  container.innerHTML = `
    <div class="col-md-3">
      <input list="productCodes" name="products[${productIndex}][productCode]" class="form-control product-code"
        placeholder="Product Code" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity"
        placeholder="Qty" min="1" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="products[${productIndex}][cost]" class="form-control cost"
        placeholder="Price" min="0" required>
    </div>
    <div class="col-md-2">
      <input type="text" class="form-control subtotal" placeholder="Subtotal" disabled>
    </div>

    <p>

    <div class="col-md-10 small">
      <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" readonly>
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-danger remove-item">Cancel</button>
    </div>
  `;
  document.getElementById('product-list').appendChild(container);
  productIndex++;
});

// Hapus produk
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('remove-item')) {
    e.target.closest('.product-item').remove();
  }
});

// Auto-hitungan subtotal dan harga
document.addEventListener('input', function (e) {
  const row = e.target.closest('.product-item');
  if (!row) return;

  const codeInput = row.querySelector('.product-code');
  const qtyInput = row.querySelector('.quantity');
  const costInput = row.querySelector('.cost');
  const subtotalInput = row.querySelector('.subtotal');

  // Saat productCode berubah → ambil harga default
  if (e.target === codeInput) {
    const code = codeInput.value;
    fetch(`/get-product/${code}`)
      .then(res => res.json())
      .then(data => {
        const nameInput = row.querySelector('.product-name');
        if (nameInput) {
          nameInput.value = data.productName || '';
        }
        if (data.cost !== undefined) {
          costInput.value = data.cost;
          subtotalInput.value = (qtyInput.value || 0) * data.cost;
        }
      });
  }

  // Saat qty atau cost berubah → hitung subtotal
  if (e.target === qtyInput || e.target === costInput) {
    const qty = parseFloat(qtyInput.value) || 0;
    const cost = parseFloat(costInput.value) || 0;
    subtotalInput.value = qty * cost;
  }
});

// Trigger perhitungan awal
document.querySelectorAll('.product-item').forEach(row => {
  const qtyInput = row.querySelector('.quantity');
  const costInput = row.querySelector('.cost');
  const subtotalInput = row.querySelector('.subtotal');

  const qty = parseFloat(qtyInput.value) || 0;
  const cost = parseFloat(costInput.value) || 0;
  subtotalInput.value = qty * cost;

});
</script>
@endsection


