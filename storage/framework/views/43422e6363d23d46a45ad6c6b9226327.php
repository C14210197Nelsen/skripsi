

<?php $__env->startSection('container'); ?>

  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-semibold text-dark mb-0">Deleted Inventory</h2>
    <div class="d-flex gap-2">
      <a href="<?php echo e(route('inventory.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Back</a>
      <a href="<?php echo e(route('inventory.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
    </div>
  </div>
  
<table class="table table-bordered">
    <thead CLASS= "table-dark text-center">
      <tr>
        <th>Product Code</th>
        <th>Product Name</th>
        <th>Stock</th>
        <th>Price</th>
        <th>Cost</th>
        <th>Min Stock</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody class="text-center align-middle">
      <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($product->productCode); ?></td>
          <td><?php echo e($product->productName); ?></td>
          <td><?php echo e($product->getStock()); ?></td>
          <td><?php echo e(number_format($product->productPrice)); ?></td>
          <td><?php echo e(number_format($product->getHPP())); ?></td>
          <td><?php echo e($product->minStock); ?></td>
          <td>
            <a href="<?php echo e(route('inventory.edit', $product->productID)); ?>" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/inventory/deleted.blade.php ENDPATH**/ ?>