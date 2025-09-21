

<?php $__env->startSection('title', 'Create Return Order'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  
  <?php if(session('error')): ?>
    <div class="alert alert-danger shadow-sm rounded"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <?php if($errors->any()): ?>
    <div class="alert alert-danger shadow-sm rounded">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($err); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-dark">Return Order</h2>
    <span class="badge bg-danger fs-6"><?php echo e($source_prefix); ?> #<?php echo e($source_id); ?></span>
  </div>

  <form action="<?php echo e(route('return.store')); ?>" method="POST" class="card shadow-sm p-4">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="type" value="<?php echo e($type); ?>">
    <input type="hidden" name="source_id" value="<?php echo e($source_id); ?>">

    
    <div class="mb-3">
      <label class="form-label fw-semibold">Partner</label>
      <input type="text" class="form-control-plaintext fw-medium" value="<?php echo e($partner_name); ?>" readonly>
    </div>

    
    <div class="mb-3">
      <label class="form-label fw-semibold">Return Date</label>
      <input type="date" name="returnDate" class="form-control rounded-pill" value="<?php echo e(date('Y-m-d')); ?>" required>
    </div>

    
    <h5 class="mt-4 fw-semibold text-black">Products to Return</h5>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-hover align-middle text-center shadow-sm">
        <thead class="table-dark text-white">
          <tr>
            <th style="width: 5%;">No</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Qty Return</th>
          </tr>
        </thead>
        <tbody>
          <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e($index + 1); ?></td>
            <td>
              <?php echo e($item->product_name); ?>

              <input type="hidden" name="items[<?php echo e($index); ?>][product_id]" value="<?php echo e($item->product_id); ?>">
            </td>
            <td>
              <?php echo e($item->quantity); ?> - <?php echo e($item->returned); ?> = <strong><?php echo e($item->quantity - $item->returned); ?></strong>
            </td>
            <td>
              <input type="number"
                     name="items[<?php echo e($index); ?>][qty_return]"
                     class="form-control text-center"
                     min="0"
                     max="<?php echo e($item->quantity - $item->returned); ?>"
                     value="<?php echo e(old('items.' . $index . '.qty_return', 0)); ?>">
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>

    
    <div class="d-flex justify-content-end gap-2 mt-4">
      <a href="<?php echo e(route('return.create')); ?>" class="btn btn-outline-danger rounded-pill">Discard</a>
      <button type="submit" class="btn btn-danger rounded-pill px-4">Submit Return</button>
    </div>

  </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
  const inputs = document.querySelectorAll('input[name^="items"]');
  for (const input of inputs) {
    const max = parseInt(input.max);
    const value = parseInt(input.value);
    if (value < 0) {
      alert('Qty Return tidak boleh negatif.');
      e.preventDefault();
      return false;
    }
    if (value > max) {
      alert('Qty Return tidak boleh lebih dari stok yang tersedia.');
      e.preventDefault();
      return false;
    }
  }
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/return/form.blade.php ENDPATH**/ ?>