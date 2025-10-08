

<?php $__env->startSection('container'); ?>
<div class="container mt-4">
  <h2>Sales Order</h2>

  <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
      <div class="alert alert-danger">
          <ul>
              <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo e($e); ?></li>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
      </div>
  <?php endif; ?>
  
  <form action="<?php echo e(route('sales.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

  <div class="row">
    
    <div class="col-md-3">
      
      <div class="mb-3">
        <label for="customer" class="form-label">Customer</label>
        <input list="customerList"
              id="customer"
              class="form-control"
              value="<?php echo e(old('customer_id') ? optional($customers->firstWhere('customerID', old('customer_id')))->customerName : ''); ?>"
              required>

        <datalist id="customerList">
          <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option data-id="<?php echo e($customer->customerID); ?>" value="<?php echo e($customer->customerName); ?>">
              <?php echo e($customer->customerName); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </datalist>

        <input type="hidden" name="customer_id"
              id="customer_id"
              value="<?php echo e(old('customer_id')); ?>">
      </div>

      
      <div class="mb-3">
        <label for="salesDate" class="form-label">Date</label>
        <input type="date" name="salesDate" id="salesDate" class="form-control"
              value="<?php echo e(old('salesDate', date('Y-m-d'))); ?>">
      </div>

      
      <div class="mb-3">
        <label for="discount_order" class="form-label">Discount (Rp)</label>
        <input type="number" name="discount_order" id="discount_order"
              class="form-control" value="<?php echo e(old('discount_order', 0)); ?>" min="0">
      </div>
    </div>


<div class="col-md-3">

  
  <div class="mb-3">
    <label for="reference" class="form-label">Reference</label>
    <input type="text" name="reference" id="reference" 
           class="form-control" maxlength="100"
           value="<?php echo e(old('reference')); ?>" placeholder="Misal: No Pesanan Shopee">
  </div>

  
  <div class="row mb-3 align-items-center">
    <div class="col-3">
      <label for="isDelivered" class="form-label">Delivered?</label>
      <select name="isDelivered" id="isDelivered" class="form-control">
        <option value="0" <?php echo e(old('isDelivered') == 0 ? 'selected' : ''); ?>>No</option>
        <option value="1" <?php echo e(old('isDelivered') == 1 ? 'selected' : ''); ?>>Yes</option>
      </select>
    </div>
    <div class="col-9">
      <label class="form-label">Delivered At</label>
      <input type="datetime-local" id="delivered_at_display" class="form-control" 
            value="<?php echo e(old('delivered_at')); ?>" disabled>
      <input type="hidden" name="delivered_at" id="delivered_at" value="<?php echo e(old('delivered_at')); ?>">
    </div>
  </div>


  
  <div class="row mb-3 align-items-center">
    <div class="col-3">
      <label for="isPaid" class="form-label">Paid?</label>
      <select name="isPaid" id="isPaid" class="form-control">
        <option value="0" <?php echo e(old('isPaid') == 0 ? 'selected' : ''); ?>>No</option>
        <option value="1" <?php echo e(old('isPaid') == 1 ? 'selected' : ''); ?>>Yes</option>
      </select>
    </div>
    <div class="col-9">
      <label class="form-label">Paid At</label>
      <input type="datetime-local" id="paid_at_display" class="form-control" 
            value="<?php echo e(old('paid_at')); ?>" disabled>
      <input type="hidden" name="paid_at" id="paid_at" value="<?php echo e(old('paid_at')); ?>">
    </div>
  </div>


</div>


    
    <div class="col-md-6">
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" maxlength="100" class="form-control" rows="8"><?php echo e(old('description', $salesorder->description ?? '')); ?></textarea>
      </div>
    </div>
  </div>



    <hr class="my-4">
    <h5 class="text-black">Line Items</h5>


    <?php
        $oldProducts = old('products', [['productCode' => '', 'quantity' => '', 'price' => '']]);
    ?>

    <div id="product-list">
      <?php $__currentLoopData = $oldProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $index = $loop->index; ?>
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
            <input list="productCodes" name="products[<?php echo e($index); ?>][productCode]" 
              class="form-control product-code" placeholder="Product Code" 
              value="<?php echo e($item['productCode'] ?? ''); ?>" required>
          </div>

          <div class="col-md-2">
            <input type="number" class="form-control original-price readonly-input"
              placeholder="Product Price" value="<?php echo e($item['original_price'] ?? ''); ?>" readonly>
          </div>

          <div class="col-md-1">
            <input type="number" name="products[<?php echo e($index); ?>][quantity]" 
              class="form-control quantity" placeholder="Qty" 
              value="<?php echo e($item['quantity'] ?? ''); ?>" min="1" required>
          </div>

          <div class="col-md-2">
            <input type="number" name="products[<?php echo e($index); ?>][price]" 
              class="form-control price" placeholder="Sell Price" 
              value="<?php echo e($item['price'] ?? ''); ?>" min="0" required>
          </div>

          <div class="col-md-2">
            <input type="text" class="form-control subtotal" placeholder="Subtotal" value="" disabled>
          </div>
        </div>


        <p>
        
        <div class="col-md-10 small">
          <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" value="" readonly>
        </div>

        <div class="col-md-2">
          <button type="button" class="btn btn-danger remove-item">Cancel</button>
        </div>

      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <datalist id="productCodes">
      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($product->productCode); ?>"><?php echo e($product->productName); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </datalist>

    </br>
      <button type="button" id="add-product" class="btn btn-outline-secondary text-black mb-3">+ Add Product</button>
    </br>


    <div class="d-flex justify-content-end gap-2">
      <a href="<?php echo e(route('sales.index')); ?>" class="btn btn-outline-danger">← Back</a>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Payment Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">

            <div class="mb-3">
              <label for="payment_type" class="form-label">Payment Type</label>
              <select name="payment_type" id="payment_type" class="form-control">
                <option value="Cash">Cash</option>
                <option value="Transfer">Transfer</option>
                <option value="QRIS">QRIS</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="total_order" class="form-label">Total</label>
              <input type="text" id="total_order" class="form-control" readonly>
            </div>

            <div class="mb-3">
              <label for="amount_paid" class="form-label">Amount Paid</label>
              <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" min="0">
            </div>

            <div class="mb-3">
              <label for="change_amount" class="form-label">Change</label>
              <input type="text" name="change_amount" id="change_amount" class="form-control" readonly>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" id="confirmPayment" class="btn btn-primary">Confirm Payment</button>
          </div>
        </div>
      </div>
    </div>

  </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
          placeholder="Product Code" required>
      </div>
      <div class="col-md-2">
        <input type="number" class="form-control original-price readonly-input" 
          placeholder="Product Price" readonly>
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
      <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" value="" readonly>
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

  // Saat productCode berubah → ambil harga default
  if (e.target === codeInput) {
    const code = codeInput.value;
    fetch(`/get-product/${code}`)

      .then(res => res.json())
      .then(data => {
        if (data.price !== undefined) {
          priceInput.value = data.price;
          subtotalInput.value = (qtyInput.value || 0) * data.price;

          const originalPriceInput = row.querySelector('.original-price');
          if (originalPriceInput) {
            originalPriceInput.value = data.price;
          }

          const nameInput = row.querySelector('.product-name');
          if (nameInput) {
            nameInput.value = data.productName || '';
          }
        }
      });
  }


  // Saat qty atau price berubah → hitung subtotal
  if (e.target === qtyInput || e.target === priceInput) {
    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    subtotalInput.value = qty * price;
  }
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
</script>

<script>
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.product-code').forEach(input => {
    input.dispatchEvent(new Event('input'));
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

document.getElementById('isDelivered').addEventListener('change', toggleAndSetDates);
document.getElementById('isPaid').addEventListener('change', toggleAndSetDates);
document.addEventListener('DOMContentLoaded', toggleAndSetDates);

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


</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/sales/create.blade.php ENDPATH**/ ?>