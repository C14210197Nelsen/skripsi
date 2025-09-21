

<?php $__env->startSection('title', 'Return Order'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  
  <?php if(session('success')): ?>
    <div class="alert alert-success shadow-sm rounded"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->has('from')): ?>
    <div class="alert alert-danger shadow-sm rounded"><?php echo e($errors->first('from')); ?></div>
  <?php endif; ?>

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Return Order</h2>
    <a href="<?php echo e(route('return.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
  </div>

  
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="<?php echo e(route('return.index')); ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="type" class="form-select form-select-sm rounded-pill">
            <option value="">-- All Types --</option>
            <option value="sales" <?php echo e(request('type') == 'sales' ? 'selected' : ''); ?>>Sales Return</option>
            <option value="purchase" <?php echo e(request('type') == 'purchase' ? 'selected' : ''); ?>>Purchase Return</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
          <?php if(request()->hasAny(['type', 'partner_id'])): ?>
            <a href="<?php echo e(route('return.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">Doc No</th>
          <th style="width: 30%;">Partner</th>
          <th style="width: 10%;">Source</th>
          <th style="width: 20%;">Return Date</th>
          <th style="width: 15%;">Total</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $returnorders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e(($return->returnID)); ?></td>
            <td><?php echo e($return->partner->name ?? '-'); ?></td>
            <td><?php echo e($return->type === 'sales' ? 'SO' : 'PO'); ?> #<?php echo e($return->sourceID); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($return->returnDate)->format('d-m-Y')); ?></td>
            <td>Rp <?php echo e(number_format($return->total, 0, ',', '.')); ?></td>
            <td>
              <a href="<?php echo e(route('return.show', $return->returnID)); ?>" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
              <form action="<?php echo e(route('return.destroy', $return->returnID)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus return ini?');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-muted">No Return Order</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  
  <div class="circle-pagination mt-4">
    <?php echo e($returnorders->links('pagination::bootstrap-5')); ?>

  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/return/index.blade.php ENDPATH**/ ?>