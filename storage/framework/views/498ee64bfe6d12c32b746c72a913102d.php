

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

    
    <div class="mb-3">
      <label for="customer" class="form-label">Customer</label>
        <select name="customer_id" id="customer" class="form-select" required>
          <option value="">-- Choose Customer --</option>
          <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($customer->customerID); ?>" <?php echo e(old('customer_id') == $customer->customerID ? 'selected' : ''); ?>>
              <?php echo e($customer->customerName); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    
    <div class="mb-3">
      <label for="salesDate" class="form-label">Date</label>
      <input type="date" name="salesDate" id="salesDate" class="form-control" value="<?php echo e(date('Y-m-d')); ?>">
    </div>

    
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea name="description" maxlength="100" class="form-control" rows="3"><?php echo e(old('description', $salesorder->description ?? '')); ?></textarea>
    </div>

    <hr class="my-4">
    <h5 class="text-black">Product</h5>


    <?php
        $oldProducts = old('products', [['productCode' => '', 'quantity' => '', 'price' => '']]);
    ?>

    <div id="product-list">
      <?php $__currentLoopData = $oldProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $index = $loop->index; ?>
      <div class="row mb-2 product-item">
        <div class="col-md-3">
          <input list="productCodes" name="products[<?php echo e($index); ?>][productCode]" class="form-control product-code"
            placeholder="Product Code" value="<?php echo e($item['productCode'] ?? ''); ?>" required>
        </div>
        <div class="col-md-2">
          <input type="number" class="form-control original-price readonly-input  " 
            placeholder="Real Price" value="<?php echo e($item['original_price'] ?? ''); ?>" readonly>
        </div>
        <div class="col-md-1">
          <input type="number" name="products[<?php echo e($index); ?>][quantity]" class="form-control quantity"
            placeholder="Qty" value="<?php echo e($item['quantity'] ?? ''); ?>" min="1" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="products[<?php echo e($index); ?>][price]" class="form-control price"
            placeholder="Price" value="<?php echo e($item['price'] ?? ''); ?>" min="0" required>
        </div>
        <div class="col-md-2">
          <input type="text" class="form-control subtotal" placeholder="Subtotal" value=""
            disabled>
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
    <div class="col-md-6 align-items-end">
      <label for="discount_order">Discount (Rp)</label>
      <input type="number" name="discount_order" id="discount_order" class="form-control" value="0" min="0">
    </div>

    <div class="d-flex justify-content-end gap-2">
      <a href="<?php echo e(route('sales.index')); ?>" class="btn btn-outline-danger">← Back</a>
      <button type="submit" class="btn btn-primary">Save</button>
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
    <div class="col-md-3">
      <input list="productCodes" name="products[${productIndex}][productCode]" class="form-control product-code"
        placeholder="Product Code" required>
    </div>
    <div class="col-md-2">
      <input type="number" class="form-control original-price readonly-input  " 
        placeholder="Real Price" value="<?php echo e($item['original_price'] ?? ''); ?>" readonly>
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
</script>

<script>
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.product-code').forEach(input => {
    input.dispatchEvent(new Event('input'));
  });
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/sales/create.blade.php ENDPATH**/ ?>