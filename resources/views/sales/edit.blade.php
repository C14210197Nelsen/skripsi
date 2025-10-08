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

  <div class="row">
    {{-- Kolom kiri (Customer + Date) --}}
    <div class="col-md-3">
    {{-- Customer (Edit SO) --}}
    <div class="mb-3">
      <label for="customer" class="form-label">Customer</label>

      {{-- Input nama customer (searchable) --}}
      <input list="customerList"
            id="customer"
            class="form-control"
            value="{{ old('customer_name', $salesorder->customer->customerName ?? '') }}"
            required>

      <datalist id="customerList">
        @foreach($customers as $customer)
          <option data-id="{{ $customer->customerID }}" value="{{ $customer->customerName }}">
            {{ $customer->customerName }}
          </option>
        @endforeach

        {{-- Jika customer lama nonaktif, tetap tampilkan --}}
        @if(isset($salesorder->customer) && !$customers->contains('customerID', $salesorder->customer->customerID))
          <option data-id="{{ $salesorder->customer->customerID }}"
                  value="{{ $salesorder->customer->customerName }}">
            {{ $salesorder->customer->customerName }} (Inactive)
          </option>
        @endif
      </datalist>

      {{-- Hidden input yang dikirim ke server --}}
      <input type="hidden" name="customer_id"
            id="customer_id"
            value="{{ old('customer_id', $salesorder->Customer_customerID ?? '') }}">
    </div>

      <div class="mb-3">
        <label for="salesDate" class="form-label">Date</label>
        <input type="date" name="salesDate" id="salesDate" class="form-control" value="{{ old('salesDate', \Carbon\Carbon::parse($salesorder->salesDate)->format('Y-m-d')) }}">
      </div>

      <div class="mb-3">
        <label for="discount_order" class="form-label">Discount (Rp)</label>
        <input type="number" name="discount_order" id="discount_order"
        class="form-control"
        value="{{ old('discount_order', $salesorder->discount_order) }}" min="0">  
      </div>
    </div>

    {{-- Kolom tengah (Delivered & Paid) --}}
    <div class="col-md-3">

      {{-- Reference --}}
      <div class="mb-3">
        <label for="reference" class="form-label">Reference</label>
        <input type="text" name="reference" id="reference" 
              class="form-control" maxlength="100"
              value="{{ old('reference', $salesorder->Reference ?? '') }}" 
              placeholder="Misal: No Pesanan Shopee">
      </div>
      {{-- Delivered --}}
      <div class="row mb-3 align-items-center">
        <div class="col-3">
          <label for="isDelivered" class="form-label">Delivered?</label>
          <select name="isDelivered" id="isDelivered" class="form-control">
            <option value="0" {{ old('isDelivered', $salesorder->isDelivered) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('isDelivered', $salesorder->isDelivered) == 1 ? 'selected' : '' }}>Yes</option>
          </select>
        </div>
        <div class="col-9">
          <label class="form-label">Delivered At</label>
          {{-- Display (disabled) --}}
          <input type="datetime-local" id="delivered_at_display" class="form-control"
                value="{{ $salesorder->deliveredAt?->format('Y-m-d\TH:i') }}" disabled>
          {{-- Hidden (ikut submit) --}}
          <input type="hidden" name="delivered_at" id="delivered_at"
                value="{{ $salesorder->deliveredAt?->format('Y-m-d H:i:s') }}">
        </div>
      </div>


      {{-- Paid --}}
      <div class="row mb-3 align-items-center">
        <div class="col-3">
          <label for="isPaid" class="form-label">Paid?</label>
          <select name="isPaid" id="isPaid" class="form-control">
            <option value="0" {{ old('isPaid', $salesorder->isPaid) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('isPaid', $salesorder->isPaid) == 1 ? 'selected' : '' }}>Yes</option>
          </select>
        </div>
        <div class="col-9">
          <label class="form-label">Paid At</label>
          {{-- Display (disabled) --}}
          <input type="datetime-local" id="paid_at_display" class="form-control"
                value="{{ $salesorder->paidAt?->format('Y-m-d H:i') }}"
                disabled>
          {{-- Hidden (ikut submit) --}}
          <input type="hidden" name="paid_at" id="paid_at"
                value="{{ $salesorder->paidAt?->format('Y-m-d H:i:s') }}">
        </div>
      </div>


    </div>

    {{-- Kolom kanan (Description) --}}
    <div class="col-md-6">
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" maxlength="100" class="form-control" rows="8">{{ old('description', $salesorder->description ?? '') }}</textarea>
      </div>
    </div>
  </div>


    <hr class="my-4">
    <h5 class="text-black">Line Items</h5>

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
        <div class="row mb-2 fw-bold">
          <div class="col-md-3">Product</div>
          <div class="col-md-2">Product Price</div>
          <div class="col-md-1">Qty</div>
          <div class="col-md-2">Sell Price</div>
          <div class="col-md-2">Subtotal</div>
        </div>
        <div class="row mb-3">
          <div class="col-md-3">
            <input list="productCodes" name="products[{{ $index }}][productCode]" class="form-control product-code"
              placeholder="Product Code" value="{{ $item['productCode'] ?? '' }}" required readonly>
          </div>
          <div class="col-md-2">
            <input type="number" class="form-control original-price readonly-input  " 
              placeholder="Product Price" value="{{ $item['original_price'] ?? '' }}" readonly>
          </div>
          <div class="col-md-1">
            <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity"
              placeholder="Qty" value="{{ ($item['quantity'] ?? 0) - ($item['returned'] ?? 0) }}" min="0" required>
          </div>
          <div class="col-md-2">
            <input type="number" name="products[{{ $index }}][price]" class="form-control price"
              placeholder="Sell Price" value="{{ $item['price'] ?? '' }}" min="0" required>
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


    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('sales.index') }}" class="btn btn-outline-danger">← Back</a>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>

    <!-- Payment Modal (Edit) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Payment Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            {{-- Payment Type --}}
            <div class="mb-3">
              <label for="payment_type" class="form-label">Payment Type</label>
              <select name="payment_type" id="payment_type" class="form-control">
                <option value="Cash" {{ old('payment_type', $salesorder->payment_type) == 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="Transfer" {{ old('payment_type', $salesorder->payment_type) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                <option value="QRIS" {{ old('payment_type', $salesorder->payment_type) == 'QRIS' ? 'selected' : '' }}>QRIS</option>
              </select>
            </div>


            <div class="mb-3">
              <label for="total_order" class="form-label">Total</label>
              <input type="text" id="total_order" class="form-control"
                    value="{{ number_format($salesorder->total_order, 2) }}" readonly>
            </div>


            <div class="mb-3">
              <label for="amount_paid" class="form-label">Amount Paid</label>
              <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" min="0"
                    value="{{ old('amount_paid', $salesorder->amount_paid) }}">
            </div>

        
            <div class="mb-3">
              <label for="change_amount" class="form-label">Change</label>
              <input type="text" name="change_amount" id="change_amount" class="form-control"
                    value="{{ old('change_amount', $salesorder->change_amount) }}" readonly>
            </div>

            <div class="modal-footer">
              <button type="button" id="confirmPayment" class="btn btn-primary">Confirm</button>
            </div>

          </div>
        </div>
      </div>
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
      <div class="col-md-2">Product Price</div>
      <div class="col-md-1">Qty</div>
      <div class="col-md-2">Sell Price</div>
      <div class="col-md-2">Subtotal</div>
    </div>
    <div class="row mb-3">
      <div class="col-md-3">
        <input list="productCodes" name="products[${productIndex}][productCode]" class="form-control product-code"
          placeholder="Product" required>
      </div>
      <div class="col-md-2">
        <input type="number" class="form-control original-price readonly-input  " 
          placeholder="Product Price" value="" readonly>
      </div>
      <div class="col-md-1">
        <input type="number" name="products[${productIndex}][quantity]" class="form-control quantity"
          placeholder="Qty" min="1" required>
      </div>
      <div class="col-md-2">
        <input type="number" name="products[${productIndex}][price]" class="form-control price"
          placeholder="Sell Price" min="0" required>
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

  container.querySelectorAll(".quantity, .price").forEach(el => {
    el.addEventListener("input", checkTotalChange);
  });
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

document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('customer');
  const hidden = document.getElementById('customer_id');
  const datalist = document.getElementById('customerList');

  input.addEventListener('input', function () {
    const option = [...datalist.options].find(opt => opt.value === input.value);
    hidden.value = option ? option.dataset.id : '';
  });
});

function getLocalDateTime() {
  const now = new Date();
  const offset = now.getTimezoneOffset();
  const local = new Date(now.getTime() - offset * 60000);
  return local.toISOString().slice(0,16);
}

function toggleAndSetDates() {
  // Delivered
  const isDelivered = document.getElementById('isDelivered').value;
  const deliveredDisplay = document.getElementById('delivered_at_display');
  const deliveredHidden = document.getElementById('delivered_at');

  if (isDelivered === "1") {
    // Jangan overwrite kalau sudah ada value dari DB
    if (!deliveredHidden.value) {
      const val = getLocalDateTime();
      deliveredHidden.value = val;
      deliveredDisplay.value = val;
    }
  } else {
    deliveredHidden.value = "";
    deliveredDisplay.value = "";
  }

  // Paid
  const isPaid = document.getElementById('isPaid').value;
  const paidDisplay = document.getElementById('paid_at_display');
  const paidHidden = document.getElementById('paid_at');

  if (isPaid === "1") {
    if (!paidHidden.value) {
      const val = getLocalDateTime();
      paidHidden.value = val;
      paidDisplay.value = val;
    }
  } else {
    paidHidden.value = "";
    paidDisplay.value = "";
  }
}

// Hitung total order
function calculateTotalOrder() {
  let total = 0;
  document.querySelectorAll('.product-item').forEach(row => {
    const qty = parseFloat(row.querySelector('.quantity')?.value) || 0;
    const price = parseFloat(row.querySelector('.price')?.value) || 0;
    total += qty * price;
  });

  // diskon
  const discount = parseFloat(document.getElementById('discount_order')?.value) || 0;
  total = total - discount;

  return total > 0 ? total : 0;
}

// Saat pilih isPaid = 1 → tampilkan modal
document.getElementById('isPaid').addEventListener('change', function () {
  if (this.value === "1") {
    const total = calculateTotalOrder();
    document.getElementById('total_order').value = total;
    document.getElementById('amount_paid').value = total;
    document.getElementById('change_amount').value = 0;

    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
  }
});

// Auto-hitung kembalian
document.getElementById('amount_paid').addEventListener('input', function () {
  const total = parseFloat(document.getElementById('total_order').value) || 0;
  const paid = parseFloat(this.value) || 0;
  document.getElementById('change_amount').value = paid - total;
});

// Confirm → tutup modal
document.getElementById('confirmPayment').addEventListener('click', function () {
  const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
  modal.hide();
});


document.getElementById('isDelivered').addEventListener('change', toggleAndSetDates);
document.getElementById('isPaid').addEventListener('change', toggleAndSetDates);


let initialTotal = 0;

window.addEventListener("DOMContentLoaded", function () {
  // Hitung total awal dari data edit
  initialTotal = calculateTotalOrder();
  console.log("Initial total:", initialTotal);

  // Pasang listener ke semua input qty, price, discount
  document.querySelectorAll(".quantity, .price, #discount_order").forEach(el => {
    el.addEventListener("input", checkTotalChange);
  });
});

function checkTotalChange() {
  let newTotal = calculateTotalOrder();
  console.log("Old:", initialTotal, "New:", newTotal);

  if (newTotal !== initialTotal) {
    document.getElementById("isPaid").value = "0";
    document.getElementById("paid_at").value = "";
    document.getElementById("paid_at_display").value = "";
    document.getElementById("payment_type").value = "";
    document.getElementById("amount_paid").value = "";
    document.getElementById("change_amount").value = "";

    alert("Total order berubah, status Paid direset. Silakan input detail pembayaran kembali.");

    // Update total lama ke nilai baru setelah reset
    initialTotal = newTotal;
  }
}




document.querySelectorAll(".quantity, .price, #discount_order").forEach(el => {
  el.addEventListener("input", checkTotalChange);
});


function toggleReadOnlyByDelivered() {
  const isDelivered = document.getElementById('isDelivered').value === "1";
  
  // Semua input
  const allInputs = document.querySelectorAll('input, select, textarea, button');

  allInputs.forEach(el => {
    if (
      el.id === 'isDelivered' || 
      el.id === 'isPaid' || 
      el.name === 'description' || 
      el.type === 'hidden' ||
      el.closest('#paymentModal')
    ) return;

    if (isDelivered) {
      if (el.tagName === 'SELECT' || el.tagName === 'BUTTON') {
        el.disabled = true;
      } else {
        el.readOnly = true;
      }
    } 

    else {
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


document.addEventListener("DOMContentLoaded", toggleReadOnlyByDelivered);
// document.getElementById("isDelivered").addEventListener("change", toggleReadOnlyByDelivered);

</script>
@endsection