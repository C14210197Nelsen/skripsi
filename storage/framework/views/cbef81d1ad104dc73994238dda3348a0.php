

<?php $__env->startSection('container'); ?>
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Deleted Supplier</h2>
    <div class="d-flex gap-2">
      <a href="<?php echo e(route('supplier.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
      <a href="<?php echo e(route('supplier.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>

<table class="table table-bordered">
    <thead CLASS= "table-dark text-center">
      <tr>
        <th>No</th>
        <th>Name</th>
        <th>Address</th>
        <th>Telephone</th>
        
        <th>Action</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($loop->iteration); ?></td>
          <td><?php echo e($supplier->supplierName); ?></td>
          <td><?php echo e($supplier->address); ?></td>
          <td><?php echo e($supplier->telephone); ?></td>
          
          <td>
            <a href="<?php echo e(route('supplier.edit', $supplier->supplierID)); ?>" class="btn btn-sm btn-outline-warning rounded-pill">Edit</a>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/supplier/deleted.blade.php ENDPATH**/ ?>