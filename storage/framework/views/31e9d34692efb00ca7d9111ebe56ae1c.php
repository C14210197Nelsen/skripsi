

<?php $__env->startSection('container'); ?>
<div class="container">
  <h2>Edit Supplier</h2>

  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>  

  <form action="<?php echo e(route('supplier.update', $supplier->supplierID)); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <div class="mb-3">
      <label for="supplierName" class="form-label">Supplier Name</label>
      <input type="text" class="form-control" id="supplierName" name="supplierName"
        value="<?php echo e(old('supplierName', $supplier->supplierName)); ?>" required>
    </div>

    <div class="mb-3">
      <label for="address" class="form-label">Address</label>
      <textarea class="form-control" id="address" name="address" ><?php echo e(old('address', $supplier->address)); ?></textarea>
    </div>

    <div class="mb-3">
      <label for="telephone" class="form-label">Telephone</label>
      <input type="text" class="form-control" id="telephone" name="telephone"
        value="<?php echo e(old('telephone', $supplier->telephone)); ?>">
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Status</label>
      <select class="form-select" id="status" name="status" required>
        <option value="1" <?php echo e($supplier->status == 1 ? 'selected' : ''); ?>>Active</option>
        <option value="0" <?php echo e($supplier->status == 0 ? 'selected' : ''); ?>>Non Active</option>
      </select>
    </div>

    <button type="submit" class="btn btn-danger">Update</button>
    <a href="<?php echo e(route('supplier.index')); ?>" class="btn btn-secondary">Discard</a>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/supplier/edit.blade.php ENDPATH**/ ?>