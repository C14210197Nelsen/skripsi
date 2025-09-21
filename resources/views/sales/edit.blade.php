@extends('layouts.main')

@section('container')
<div class="container mt-4">
  <h2>Sales Order (Edit)</h2>

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
    
  <form action="{{ route('sales.update', $salesorder->salesID) }}" method="POST">
    @csrf
@method('PUT')

    {{-- Customer --}}
    <div class="mb-3">
      <label for="customer" class="form-label">Customer</label>
        <select name="customer_id" id="customer" class="form-select" required>
          <option value="">-- Choose Customer --</option>
          @foreach($customers as $customer)
            <option value="{{ $customer->customerID }}"
              {{ old('customer_id', $salesorder->Customer_customerID) == $customer->customerID ? 'selected' : '' }}>
              {{ $customer->customerName }}{{ $customer->status == 0 ? ' (Nonactive)' : '' }}
            </option>
          @endforeach
        </select>

    </div>

    <div class="mb-3">
      <label for="salesDate" class="form-label">Date</label>
      <input type="date" name="salesDate" id="salesDate" class="form-control" value="{{ old('salesDate', \Carbon\Carbon::parse($salesorder->salesDate)->format('Y-m-d')) }}">
    </div>

    {{-- Description --}}
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea name="description" maxlength="100" class="form-control" rows="3">{{ old('description', $salesorder->description ?? '') }}</textarea>
    </div>


    <hr class="my-4">
    <h5 class="text-black">Product</h5>

    @php
      $oldProducts = old('products', $salesorder->details->map(function($d) {
          return [
              'productCode' => $d->product->productCode,
              'quantity' => $d->quantity,
              'returned' => $d->returned,
              'price' => $d->price,
              'original_price' => $d->original_price,
              'productName' => $d->product->productName,
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
          <input type="number" class="form-control original-price readonly-input  " 
            placeholder="Real Price" value="{{ $item['original_price'] ?? '' }}" readonly>
        </div>
        <div class="col-md-1">
          <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity"
            placeholder="Qty" value="{{ ($item['quantity'] ?? 0) - ($item['returned'] ?? 0) }}" min="0" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="products[{{ $index }}][price]" class="form-control price"
            placeholder="Price" value="{{ $item['price'] ?? '' }}" min="0" required>
        </div>
        <div class="col-md-2">
          <input type="text" class="form-control subtotal" placeholder="Subtotal" disabled>
        </div>
        <p>
        <div class="col-md-10 small">
          <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name"
            value="{{ $item['productName'] ?? ($item['product']['productName'] ?? '') }}" readonly>
        </div>

      </div>
      @endforeach
    </div>

    <datalist id="productCodes">
      @foreach($products as $product)
        <option value="{{ $product->productCode }}">{{ $product->productName }}</option>
      @endforeach
    </datalist>

    </br>
      <button type="button" id="add-product" class="btn btn-outline-secondary text-black mb-3">+ Add Product</button>
    </br>
    <div class="col-md-6 align-items-end">
      <label for="discount_order">Discount (Rp)</label>
      <input type="number" name="discount_order" id="discount_order"
       class="form-control"
       value="{{ old('discount_order', $salesorder->discount_order) }}" min="0">  
    </div>

    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('sales.index') }}" class="btn btn-outline-danger">← Back</a>
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
        placeholder="Product" required>
    </div>
    <div class="col-md-2">
      <input type="number" class="form-control original-price readonly-input  " 
        placeholder="Real Price" value="{{ $item['original_price'] ?? '' }}" readonly>
    </div>
    <div class="col-md-1">
      <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity"
        placeholder="Qty" min="1" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="products[${productIndex}][price]" class="form-control price"
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
  const priceInput = row.querySelector('.price');
  const subtotalInput = row.querySelector('.subtotal');

  // Saat productCode berubah -> ambil harga default
  if (e.target === codeInput) {
    const code = codeInput.value;
    fetch(`/get-product/${code}`)
      .then(res => res.json())
      .then(data => {
        const nameInput = row.querySelector('.product-name');
        if (nameInput) {
          nameInput.value = data.productName || '';
        }

        if (data.price !== undefined) {
          priceInput.value = data.price;
          subtotalInput.value = (qtyInput.value || 0) * data.price;
          const originalPriceInput = row.querySelector('.original-price');
          if (originalPriceInput) {
            originalPriceInput.value = data.price;
          }
        }
      });
  }

  // Saat qty atau price berubah -> hitung subtotal
  if (e.target === qtyInput || e.target === priceInput) {
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    subtotalInput.value = qty * price;
  }
});

window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.product-code').forEach(input => {
    input.dispatchEvent(new Event('input'));
  });
});


// Trigger perhitungan awal
document.querySelectorAll('.product-item').forEach(row => {
  const qtyInput = row.querySelector('.quantity');
  const priceInput = row.querySelector('.price');
  const subtotalInput = row.querySelector('.subtotal');

  const qty = parseFloat(qtyInput.value) || 0;
  const price = parseFloat(priceInput.value) || 0;
  subtotalInput.value = qty * price;
});

</script>
@endsection