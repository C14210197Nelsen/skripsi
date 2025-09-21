

<?php $__env->startSection('container'); ?>
<div class="container mt-4">
  <h2>Purchase Order</h2>

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

    
  <form action="<?php echo e(route('purchase.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    
    <div class="mb-3">
      <label for="supplier" class="form-label">Supplier</label>
        <select name="supplier_id" id="supplier" class="form-select" required>
          <option value="">-- Choose Supplier --</option>
          <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($supplier->supplierID); ?>" <?php echo e(old('supplier_id') == $supplier->supplierID ? 'selected' : ''); ?>>
              <?php echo e($supplier->supplierName); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    
    <div class="mb-3">
      <label for="purchaseDate" class="form-label">Date</label>
      <input type="date" name="purchaseDate" id="purchaseDate" class="form-control" value="<?php echo e(date('Y-m-d')); ?>">
    </div>


    <hr class="my-4">
    <h5 class="text-black">Product</h5>


    <?php
        $oldProducts = old('products', [['productCode' => '', 'quantity' => '', 'cost' => '']]);
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
          <input type="number" name="products[<?php echo e($index); ?>][quantity]" class="form-control quantity"
            placeholder="Qty" value="<?php echo e($item['quantity'] ?? ''); ?>" min="1" required>
        </div>
        <div class="col-md-2">
          <input type="number" name="products[<?php echo e($index); ?>][cost]" class="form-control cost"
            placeholder="Price" value="<?php echo e($item['cost'] ?? ''); ?>" min="0" required>
        </div>
        <div class="col-md-2">
          <input type="text" class="form-control subtotal" placeholder="Subtotal" value=""
            disabled>
        </div>

        <p>

        <div class="col-md-10 small">
          <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" value="<?php echo e($item['productName'] ?? ''); ?>" readonly>
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

    <br>
      <button type="button" id="add-product" class="btn btn-outline-secondary text-black mb-3">+ Add Product</button>
    </br>

    <div class="d-flex justify-content-end gap-2">
      <a href="<?php echo e(route('purchase.index')); ?>" class="btn btn-outline-danger">← Back</a>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>

  </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
let productIndex = <?php echo e(count(old('products', [[]]))); ?>;

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
      <input type="text" class="form-control product-name readonly-input" style="font-size: 0.85rem;" placeholder="Product Name" value="<?php echo e($item['productName'] ?? ''); ?>" readonly>
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
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/purchase/create.blade.php ENDPATH**/ ?>