

<?php $__env->startSection('title', 'Return Order'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  
  <?php if(session('success')): ?>
    <div class="alert alert-success shadow-sm rounded"><?php echo e(session('success')); ?></div>
  <?php endif; ?>

  
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div class="btn-group">
      <a href="<?php echo e(route('return.create')); ?>" class="btn btn-sm <?php echo e(request('type') == '' ? 'btn-danger' : 'btn-outline-danger'); ?>">All</a>
      <a href="<?php echo e(route('return.create', ['type' => 'sales'])); ?>" class="btn btn-sm <?php echo e(request('type') == 'sales' ? 'btn-danger' : 'btn-outline-danger'); ?>">Sales Order</a>
      <a href="<?php echo e(route('return.create', ['type' => 'purchase'])); ?>" class="btn btn-sm <?php echo e(request('type') == 'purchase' ? 'btn-danger' : 'btn-outline-danger'); ?>">Purchase Order</a>
    </div>

    <form action="<?php echo e(route('return.create')); ?>" method="GET" class="row g-2 align-items-center">
      <?php if(request('type')): ?>
        <input type="hidden" name="type" value="<?php echo e(request('type')); ?>">
      <?php endif; ?>

      <div class="col-md-auto">
        <select name="partner_id" class="form-select form-select-sm rounded-pill" style="min-width: 200px;">
          <option value="">-- All Customers/Suppliers --</option>
          <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($partner->id); ?>" <?php echo e(request('partner_id') == $partner->id ? 'selected' : ''); ?>>
              <?php echo e($partner->name); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="col-md-auto d-flex gap-2">
        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
        <?php if(request()->hasAny(['type', 'partner_id'])): ?>
          <a href="<?php echo e(route('return.create')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th style="width: 10%;">Source</th>
          <th style="width: 30%;">Partner</th>
          <th style="width: 10%;">Type</th>
          <th style="width: 20%;">Date</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 10%;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e(($orders->firstItem() ?? 0) + $loop->index); ?></td>
            <td><?php echo e($order->type === 'sales' ? 'SO' : 'PO'); ?> #<?php echo e($order->sourceID); ?></td>
            <td><?php echo e($order->partner_name ?? '-'); ?></td>
            <td><?php echo e(ucfirst($order->type)); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($order->date)->format('d-m-Y')); ?></td>
            <td>Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?></td>
            <td>
              <a href="<?php echo e(route('return.form', ['type' => $order->type, 'id' => $order->sourceID])); ?>"
                 class="btn btn-sm btn-outline-danger rounded-pill">Return</a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="7" class="text-muted">No Active Order</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  
  <div class="circle-pagination mt-4">
    <?php echo e($orders->links('pagination::bootstrap-5')); ?>

  </div>
    <div class="d-flex justify-content-end mt-4">
    <a href="<?php echo e(route('return.index')); ?>" class="btn btn-outline-danger rounded-pill px-4">
      ← Back to Return
    </a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/return/create.blade.php ENDPATH**/ ?>