

<?php $__env->startSection('container'); ?>
<div class="container">
  <h2>Edit Product</h2>

  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>  

  <form action="<?php echo e(route('inventory.update', $product->productID)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="mb-3">
      <label for="productCode" class="form-label">Product Code</label>
      <input type="text" class="form-control" id="productCode" name="productCode"
        value="<?php echo e(old('productCode', $product->productCode)); ?>" maxlength="16" required>
    </div>

    <div class="mb-3">
      <label for="productName" class="form-label">Product Name</label>
      <input type="text" class="form-control" id="productName" name="productName"
        value="<?php echo e(old('productName', $product->productName)); ?>" maxlength="255" required>
    </div>

    <div class="mb-3">
      <label for="productPrice" class="form-label">Price</label>
      <input type="number" class="form-control" id="productPrice" name="productPrice"
        value="<?php echo e(old('productPrice', $product->productPrice)); ?>" max="9999999999" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Cost</label>
      <input type="number" class="form-control" name="productCost" value="<?php echo e(old('productCost', $product->getHPP())); ?>" max="9999999999" required>

    </div>


    <div class="mb-3">
      <label for="LeadTime" class="form-label">Lead Time (Day)</label>
      <input type="number" class="form-control" id="LeadTime" name="LeadTime"
        value="<?php echo e(old('LeadTime', $product->LeadTime)); ?>" maxlength="365">
    </div>

    <div class="mb-3">
      <label class="form-label">Stock</label>
      <input type="number" class="form-control" name="stock" value="<?php echo e(old('stock', $product->getStock())); ?>" required>

    </div>

    <div class="mb-3">
      <label for="minStock" class="form-label">Minimum Stock</label>
      <input type="number" class="form-control" id="minStock" name="minStock"
        value="<?php echo e(old('minStock', $product->minStock)); ?>">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" <?php echo e($product->status == 1 ? 'selected' : ''); ?>>Active</option>
        <option value="0" <?php echo e($product->status == 0 ? 'selected' : ''); ?>>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Update</button>
    <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-secondary">Discard</a>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/inventory/edit.blade.php ENDPATH**/ ?>