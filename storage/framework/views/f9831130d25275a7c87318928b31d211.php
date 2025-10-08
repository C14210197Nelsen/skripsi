

<?php $__env->startSection('title', 'Purchase Order Detail'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-dark mb-0">Purchase Order Detail</h2>
    <span class="badge bg-danger fs-6">#<?php echo e($purchaseorder->purchaseID); ?></span>
  </div>
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Supplier</h6>
          <p class="fw-medium"><?php echo e($purchaseorder->supplier->supplierName); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Purchase Date</h6>
          <p class="fw-medium"><?php echo e(\Carbon\Carbon::parse($purchaseorder->purchaseDate)->format('d-m-Y')); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Received Date</h6>
          <p class="fw-medium">
            <?php echo e($purchaseorder->receivedAt 
                ? \Carbon\Carbon::parse($purchaseorder->receivedAt)->format('d-m-Y') 
                : '-'); ?>

          </p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Paid Date</h6>
          <p class="fw-medium">
            <?php echo e($purchaseorder->paidAt 
                ? \Carbon\Carbon::parse($purchaseorder->paidAt)->format('d-m-Y') 
                : '-'); ?>

          </p>
        </div>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <h6 class="text-muted mb-1">Description</h6>
          <p class="fw-medium"><?php echo e($purchaseorder->description ?? '-'); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Total Price</h6>
          <p class="fw-medium text-danger">Rp <?php echo e(number_format($purchaseorder->totalPrice, 0, ',', '.')); ?></p>
        </div>
      </div>

    </div>
  </div>

  <h5 class="text-black fw-semibold mb-3">Product List</h5>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark">
        <tr>
          <th>No</th>
          <th>Product Code</th>
          <th>Product Name</th>
          <th>Qty</th>
          <th>Returned</th>
          <th>Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $purchaseorder->purchasedetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $purchasedetail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($i + 1); ?></td>
          <td><?php echo e($purchasedetail->product->productCode); ?></td>
          <td><?php echo e($purchasedetail->product->productName); ?></td>
          <td><?php echo e($purchasedetail->quantity); ?></td>
          <td><?php echo e($purchasedetail->returned); ?></td>
          <td>Rp <?php echo e(number_format($purchasedetail->price, 0, ',', '.')); ?></td>
          <td>Rp <?php echo e(number_format($purchasedetail->subtotal, 0, ',', '.')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-end mt-4">
    <a href="<?php echo e(route('purchase.index')); ?>" class="btn btn-outline-danger rounded-pill px-4">
      ← Back to Purchase
    </a>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/purchase/show.blade.php ENDPATH**/ ?>