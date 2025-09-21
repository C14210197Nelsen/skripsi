

<?php $__env->startSection('title', 'Sales Order'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  
  <?php if(session('success')): ?>
    <div class="alert alert-success shadow-sm rounded"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->has('from')): ?>
    <div class="alert alert-danger shadow-sm rounded"><?php echo e($errors->first('from')); ?></div>
  <?php endif; ?>

  <?php if(session('warning')): ?>
    <div class="alert alert-warning"><?php echo session('warning'); ?></div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="alert alert-danger shadow-sm rounded"><?php echo e(session('error')); ?></div>
  <?php endif; ?>


  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Sales Order</h2>

    
    <div class="d-flex gap-2">
      <a href="#" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">Excel</a>
      <a href="<?php echo e(route('sales.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>


  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="<?php echo e(route('sales.index')); ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
          <select name="customer_id" class="form-select form-select-sm rounded-pill">
            <option value="">-- All Customers --</option>
            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($customer->customerID); ?>" <?php echo e(request('customer_id') == $customer->customerID ? 'selected' : ''); ?>>
                <?php echo e($customer->customerName); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="col-md-2">
          <input type="month" name="from" class="form-control form-control-sm rounded-pill" value="<?php echo e(request('from')); ?>">
        </div>
        <div class="col-md-2">
          <input type="month" name="to" class="form-control form-control-sm rounded-pill" value="<?php echo e(request('to')); ?>">
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Filter</button>
          <?php if(request('customer_id') || request('from') || request('to')): ?>
            <a href="<?php echo e(route('sales.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
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
          <th style="width: 35%;">Customer</th>
          <th style="width: 20%;">Date</th>
          <th style="width: 20%;">Total</th>
          <th style="width: 20%;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $salesorders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salesorder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e(($salesorder->salesID)); ?></td>
            <td><?php echo e($salesorder->customer->customerName ?? '-'); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($salesorder->salesDate)->format('d-m-Y')); ?></td>
            <td>Rp <?php echo e(number_format($salesorder->totalPrice, 0, ',', '.')); ?></td>
            <td>
              <a href="<?php echo e(route('sales.show', $salesorder->salesID)); ?>" class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
              <a href="<?php echo e(route('sales.edit', $salesorder->salesID)); ?>" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
              <form action="<?php echo e(route('sales.destroy', $salesorder->salesID)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sales order ini?');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="5" class="text-muted">No Sales Order</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  
  <div class="circle-pagination mt-4">
    <?php echo e($salesorders->links('pagination::bootstrap-5')); ?>

  </div>

</div>

<!-- Modal Upload Excel -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="<?php echo e(route('sales.import.submit')); ?>" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Sales Order (Excel)</h5>
        </div>
        <div class="modal-body">
          <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Import</button>
        </div>
      </div>
    </form>
  </div>
</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/sales/index.blade.php ENDPATH**/ ?>