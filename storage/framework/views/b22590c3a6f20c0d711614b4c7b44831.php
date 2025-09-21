

<?php $__env->startSection('title', 'Supplier'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Supplier</h2>
    <div class="d-flex gap-2">
      <a href="<?php echo e(route('supplier.deleted')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Deleted</a>
      <a href="<?php echo e(route('supplier.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>

  
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark text-white">
        <tr>
          <th style="width: 5%;">No</th>
          <th>Name</th>
          <th>Address</th>
          <th>Telephone</th>
          <th style="width: 18%;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($loop->iteration); ?></td>
          <td><?php echo e($supplier->supplierName); ?></td>
          <td><?php echo e($supplier->address); ?></td>
          <td><?php echo e($supplier->telephone); ?></td>
          <td class="d-flex justify-content-center gap-1 flex-wrap">
            <a href="<?php echo e(route('supplier.edit', $supplier->supplierID)); ?>" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
            <form action="<?php echo e(route('supplier.destroy', $supplier->supplierID)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Confirmation to Delete')">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/supplier/index.blade.php ENDPATH**/ ?>