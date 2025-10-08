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

  <div class="row">
    {{-- Kolom kiri (Supplier + Date) --}}
    <div class="col-md-3">
      <div class="mb-3">
        <label for="supplier" class="form-label">Supplier</label>
        <input list="supplierList"
              id="supplier"
              class="form-control"
              value="{{ old('supplier_name', $purchaseorder->supplier->supplierName ?? '') }}"
              required>

        <datalist id="supplierList">
          @foreach($suppliers as $supplier)
            <option data-id="{{ $supplier->supplierID }}" value="{{ $supplier->supplierName }}">
              {{ $supplier->supplierName }}
            </option>
          @endforeach

          {{-- Jika supplier lama nonaktif, tetap tampilkan --}}
          @if(isset($purchaseorder->supplier) && !$suppliers->contains('supplierID', $purchaseorder->supplier->supplierID))
            <option data-id="{{ $purchaseorder->supplier->supplierID }}"
                    value="{{ $purchaseorder->supplier->supplierName }}">
              {{ $purchaseorder->supplier->supplierName }} (Inactive)
            </option>
          @endif
        </datalist>

        <input type="hidden" name="supplier_id"
              id="supplier_id"
              value="{{ old('supplier_id', $purchaseorder->Supplier_supplierID ?? '') }}">
      </div>

      <div class="mb-3">
        <label for="purchaseDate" class="form-label">Date</label>
        <input type="date" name="purchaseDate" id="purchaseDate"
              class="form-control"
              value="{{ old('purchaseDate', \Carbon\Carbon::parse($purchaseorder->purchaseDate)->format('Y-m-d')) }}">
      </div>
    </div>

    {{-- Kolom tengah (Received & Paid) --}}
    <div class="col-md-3">
      {{-- Received --}}
      <div class="row mb-3 align-items-center">
        <div class="col-4">
          <label for="isReceived" class="form-label">Received?</label>
          <select name="isReceived" id="isReceived" class="form-control">
            <option value="0" {{ old('isReceived', $purchaseorder->isReceived) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('isReceived', $purchaseorder->isReceived) == 1 ? 'selected' : '' }}>Yes</option>
          </select>
        </div>
        <div class="col-8">
          <label class="form-label">Received At</label>
          <input type="datetime-local" id="received_at_display" class="form-control"
                value="{{ old('received_at', optional($purchaseorder->received_at)->format('Y-m-d\TH:i')) }}" disabled>
          <input type="hidden" name="received_at" id="received_at"
                value="{{ old('received_at', optional($purchaseorder->received_at)->format('Y-m-d\TH:i')) }}">
        </div>
      </div>

      {{-- Paid --}}
      <div class="row mb-3 align-items-center">
        <div class="col-4">
          <label for="isPaid" class="form-label">Paid?</label>
          <select name="isPaid" id="isPaid" class="form-control">
            <option value="0" {{ old('isPaid', $purchaseorder->isPaid) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('isPaid', $purchaseorder->isPaid) == 1 ? 'selected' : '' }}>Yes</option>
          </select>
        </div>
        <div class="col-8">
          <label class="form-label">Paid At</label>
          <input type="datetime-local" id="paid_at_display" class="form-control"
                value="{{ old('paid_at', optional($purchaseorder->paid_at)->format('Y-m-d\TH:i')) }}" disabled>
          <input type="hidden" name="paid_at" id="paid_at"
                value="{{ old('paid_at', optional($purchaseorder->paid_at)->format('Y-m-d\TH:i')) }}">
        </div>
      </div>
    </div>

    {{-- Kolom kanan (Description) --}}
    <div class="col-md-6">
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" maxlength="100" class="form-control" rows="5">{{ old('description', $purchaseorder->description) }}</textarea>
      </div>
    </div>
  </div>



  
    <hr class="my-4">
    <h5 class="text-black">Line Items</h5>


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
        <div class="row mb-2 fw-bold">
          <div class="col-md-3">Product</div>
          <div class="col-md-2">Qty</div>
          <div class="col-md-2">Buy Price</div>
          <div class="col-md-2">Subtotal</div>
        </div>
        <div class="row mb-3">
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
              placeholder="Buy Price" value="{{ $item['cost'] ?? '' }}" min="0" required readonly>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control subtotal" placeholder="Subtotal" disabled>
          </div>
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
    <div class="row mb-2 fw-bold">
      <div class="col-md-3">Product</div>
      <div class="col-md-2">Qty</div>
      <div class="col-md-2">Buy Price</div>
      <div class="col-md-2">Subtotal</div>
    </div>
    <div class="row mb-3">
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
          placeholder="Buy Price" min="0" required>
      </div>
      <div class="col-md-2">
        <input type="text" class="form-control subtotal" placeholder="Subtotal" disabled>
      </div>
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

document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('supplier');
  const hidden = document.getElementById('supplier_id');
  const datalist = document.getElementById('supplierList');

  input.addEventListener('input', function () {
    const option = [...datalist.options].find(opt => opt.value === input.value);
    hidden.value = option ? option.dataset.id : '';
  });
});

document.addEventListener('DOMContentLoaded', function () {
  function pad(num) {
    return num.toString().padStart(2, '0');
  }

  function getLocalDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = pad(now.getMonth() + 1);
    const day = pad(now.getDate());
    const hours = pad(now.getHours());
    const minutes = pad(now.getMinutes());
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  function toggleDateField(selectId, displayId, hiddenId) {
    const select = document.getElementById(selectId);
    const displayInput = document.getElementById(displayId);
    const hiddenInput = document.getElementById(hiddenId);

    function update() {
      if (select.value == "1") {
        const formatted = getLocalDateTime();
        displayInput.value = formatted;
        hiddenInput.value = formatted;
      } else {
        displayInput.value = "";
        hiddenInput.value = "";
      }
    }

    select.addEventListener('change', update);
  }

  toggleDateField('isReceived', 'received_at_display', 'received_at');
  toggleDateField('isPaid', 'paid_at_display', 'paid_at');
});

function toggleReadOnlyByReceived() {
  const isReceived = document.getElementById('isReceived').value === "1";

  // Semua input
  const allInputs = document.querySelectorAll('input, select, textarea, button');

  allInputs.forEach(el => {
    if (
      el.id === 'isReceived' || 
      el.id === 'isPaid' || 
      el.name === 'description' || 
      el.type === 'hidden' ||
      el.closest('.d-flex') // supaya tombol Back/Save tetap aktif
    ) return;

    if (isReceived) {
      if (el.tagName === 'SELECT' || el.tagName === 'BUTTON') {
        el.disabled = true;
      } else {
        el.readOnly = true;
      }
    } else {
      if (el.tagName === 'SELECT' || el.tagName === 'BUTTON') {
        el.disabled = false;
      } else {
        el.readOnly = false;
      }
    }
  });

  document.querySelectorAll('a.btn, button[type="submit"]').forEach(el => {
    el.disabled = false;
  });
}

document.addEventListener("DOMContentLoaded", toggleReadOnlyByReceived);
// document.getElementById("isReceived").addEventListener("change", toggleReadOnlyByReceived);


</script>
@endsection


