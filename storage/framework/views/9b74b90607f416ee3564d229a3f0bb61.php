

<?php $__env->startSection('container'); ?>
<div class="container">
  <h2>Create Product</h2>

  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>  

  <form action="<?php echo e(route('inventory.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <div class="mb-3">
      <label for="productCode" class="form-label">Product Code</label>
      <input type="text" class="form-control" id="productCode" name="productCode" value="<?php echo e(old('productCode')); ?>" maxlength="16" required>
    </div>

    <div class="mb-3">
      <label for="productName" class="form-label">Product Name</label>
      <input type="text" class="form-control" id="productName" name="productName" value="<?php echo e(old('productName')); ?>" maxlength="255" required>
    </div>

    <div class="mb-3">
      <label for="productPrice" class="form-label">Price</label>
      <input type="number" class="form-control" id="productPrice" name="productPrice" value="<?php echo e(old('productPrice')); ?>"  max="9999999999" required>
    </div>

    <div class="mb-3">
      <label for="productCost" class="form-label">Cost</label>
      <input type="number" class="form-control" id="productCost" name="productCost" value="<?php echo e(old('productCost')); ?>" max="9999999999" required>
    </div>

    <div class="mb-3">
      <label for="LeadTime" class="form-label">Lead Time (Day)</label>
      <input type="number" class="form-control" id="LeadTime" name="LeadTime" value="<?php echo e(old('LeadTime')); ?>" maxlength="365">
    </div>

    <div class="mb-3">
      <label for="stock" class="form-label">Stock</label>
      <input type="number" class="form-control" id="stock" name="stock" value="<?php echo e(old('stock')); ?>">
    </div>

    <div class="mb-3">
      <label for="minStock" class="form-label">Minimum Stock</label>
      <input type="number" class="form-control" id="minStock" name="minStock" value="<?php echo e(old('minStock')); ?>">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" <?php echo e(old('status') == '1' ? 'selected' : ''); ?>>Active</option>
        <option value="0" <?php echo e(old('status') == '0' ? 'selected' : ''); ?>>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Save</button>
    <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-secondary">Discard</a>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/inventory/create.blade.php ENDPATH**/ ?>