

<?php $__env->startSection('title', 'Sales Order Detail'); ?>

<?php $__env->startSection('container'); ?>
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-semibold text-dark mb-0">Sales Order Detail</h2>

    <div class="d-flex align-items-center gap-2">
      <a href="<?php echo e(route('sales.printInvoice', $salesorder->salesID)); ?>" class="btn btn-sm btn-primary" target="_blank">
        🧾 Invoice
      </a>
      <span class="badge bg-danger fs-6">#<?php echo e($salesorder->salesID); ?></span>
    </div>
  </div>


  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Customer</h6>
          <p class="fw-medium"><?php echo e($salesorder->customer->customerName); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Sales Date</h6>
          <p class="fw-medium">
            <?php echo e($salesorder->salesDate ? \Carbon\Carbon::parse($salesorder->salesDate)->format('d-m-Y') : ''); ?>

          </p>
        </div>

        <div class="col-md-3">
          <h6 class="text-muted mb-1">Delivered At</h6>
          <p class="fw-medium">
            <?php echo e($salesorder->deliveredAt ? \Carbon\Carbon::parse($salesorder->deliveredAt)->format('d-m-Y') : ''); ?>

          </p>
        </div>

        <div class="col-md-3">
          <h6 class="text-muted mb-1">Paid At</h6>
          <p class="fw-medium">
            <?php echo e($salesorder->paidAt ? \Carbon\Carbon::parse($salesorder->paidAt)->format('d-m-Y') : ''); ?>

          </p>
        </div>

        <div class="col-md-3">
          <h6 class="text-muted mb-1">Total Price</h6>
          <p class="fw-medium text-warning">Rp <?php echo e(number_format($salesorder->totalPrice, 0, ',', '.')); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Total Cost</h6>
          <p class="fw-medium text-danger">Rp <?php echo e(number_format($salesorder->totalHPP, 0, ',', '.')); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Profit</h6>
          <p class="fw-medium text-success">Rp <?php echo e(number_format($salesorder->totalProfit, 0, ',', '.')); ?></p>
        </div>
        <div class="col-md-3">
          <h6 class="text-muted mb-1">Discount</h6>
          <p class="fw-medium text-danger">Rp <?php echo e(number_format($salesorder->discount_order)); ?></p>
        </div>
      </div>

      <hr class="my-3">

      <div class="row mt-2">
        <div class="col-md-2">
          <h6 class="text-muted mb-1">Reference</h6>
          <p class="fw-medium"><?php echo e($salesorder->Reference ?? '-'); ?></p>
        </div>
        <div class="col-md-10">
          <h6 class="text-muted mb-1">Description</h6>
          <p class="fw-medium"><?php echo e($salesorder->description ?? '-'); ?></p>
        </div>
    </div>

    </div>
  </div>

  <h5 class="text-black fw-semibold mb-3">Product List</h5>

  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle text-center shadow-sm">
      <thead class="table-dark">
        <tr>
          <th style="width: 5%;">No</th>
          <th style="width: 10%;">Product Code</th>
          <th style="width: 55%;">Product Name</th>
          <th style="width: 5%;">Qty</th>
          <th style="width: 5%;">Returned</th>
          <th style="width: 10%;">Price</th>
          <th style="width: 10%;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $salesorder->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($i + 1); ?></td>
          <td><?php echo e($detail->product->productCode); ?></td>
          <td><?php echo e($detail->product->productName); ?></td>
          <td><?php echo e($detail->quantity); ?></td>
          <td><?php echo e($detail->returned); ?></td>
          <td>Rp <?php echo e(number_format($detail->price, 0, ',', '.')); ?></td>
          <td>Rp <?php echo e(number_format($detail->subtotal, 0, ',', '.')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-end mt-4">
    <a href="<?php echo e(route('sales.index')); ?>" class="btn btn-outline-danger rounded-pill px-4">
      ← Back to Sales
    </a>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/sales/show.blade.php ENDPATH**/ ?>