

<?php $__env->startSection('title', 'Detail Return Order'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-dark mb-0">Return Order</h2>
    <span class="badge bg-danger fs-6">#<?php echo e($return->returnID); ?></span>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <h6 class="text-muted mb-1">Type</h6>
          <p class="fw-medium"><?php echo e(ucfirst($return->type)); ?></p>
        </div>
        <div class="col-md-4">
          <h6 class="text-muted mb-1">Source ID</h6>
          <p class="fw-medium"><?php echo e($sourcePrefix); ?> #<?php echo e($return->sourceID); ?></p>
        </div>
        <div class="col-md-4">
          <h6 class="text-muted mb-1">Partner</h6>
          <p class="fw-medium"><?php echo e($partnerName); ?></p>
        </div>
        <div class="col-md-4">
          <h6 class="text-muted mb-1">Return Date</h6>
          <p class="fw-medium"><?php echo e(\Carbon\Carbon::parse($return->returnDate)->format('d-m-Y')); ?></p>
        </div>
      </div>
    </div>
  </div>

  <h5 class="text-black fw-semibold mb-3">Returned Product</h5>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th>Product Name</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $return->returndetail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($index + 1); ?></td>
          <td><?php echo e($detail->product->productName ?? '-'); ?></td>
          <td><?php echo e($detail->quantity); ?></td>
          <td>Rp <?php echo e(number_format($detail->price, 0, ',', '.')); ?></td>
          <td>Rp <?php echo e(number_format($detail->subtotal, 0, ',', '.')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-end mt-4">
    <a href="<?php echo e(route('return.index')); ?>" class="btn btn-outline-danger rounded-pill px-4">← Back to Return</a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/return/show.blade.php ENDPATH**/ ?>