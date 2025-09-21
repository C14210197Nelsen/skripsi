

<?php $__env->startSection('container'); ?>

<div class="container">
  <h2>Movement - <?php echo e($product->productName); ?> (<?php echo e($product->productCode); ?>)</h2>

  <table class="table table-bordered table-index">
    <thead class="table-dark text-center">
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Quantity</th>
        <th>Updated Stock</th>
        <th>Cost</th>
        <th>Total</th>
        <th>Source</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      <?php $__empty_1 = true; $__currentLoopData = $ledgers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ledger): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td><?php echo e(\Carbon\Carbon::parse($ledger->created_at)->format('d M Y H:i')); ?></td>
          <td><?php echo e(ucfirst($ledger->type)); ?></td>
          <td><?php echo e($ledger->qty); ?></td>
          <td><?php echo e($ledger->saldo_qty); ?></td>
          <td><?php echo e(number_format($ledger->hpp)); ?></td>
          <td><?php echo e(number_format($ledger->saldo_harga)); ?></td>
          <td><?php echo e($ledger->source_type); ?> #<?php echo e($ledger->source_id); ?></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="7">No Movement.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

    <div class="circle-pagination mt-4">
      <?php echo e($ledgers->links('pagination::bootstrap-5')); ?>

    </div>

    <div class="d-flex justify-content-end gap-2">
      <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-outline-danger">← Back</a>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/inventory/stockledger.blade.php ENDPATH**/ ?>