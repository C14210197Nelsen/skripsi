

<?php $__env->startSection('container'); ?>
<div class="container-fluid">
    <h3 class="mb-4">Dashboard</h3>

    <!-- Filter Bulan & Tahun -->
    <form method="GET" action="<?php echo e(route('home')); ?>" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="bulan" class="form-select form-select-sm">
                <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(sprintf('%02d', $b)); ?>" <?php echo e($bulan == sprintf('%02d', $b) ? 'selected' : ''); ?>>
                        <?php echo e(DateTime::createFromFormat('!m', $b)->format('F')); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="tahun" class="form-select form-select-sm">
                <?php for($t = date('Y'); $t >= date('Y') - 5; $t--): ?>
                    <option value="<?php echo e($t); ?>" <?php echo e($tahun == $t ? 'selected' : ''); ?>><?php echo e($t); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-danger btn-sm rounded-pill">Filter</button>
        </div>
    </form>

    <!-- ===== Sales KPI ===== -->
    <div class="row">
        <div class="col-md-4">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-primary text-light"><i class="bi bi-basket"></i> Jumlah Order</div>
                <div class="card-body"><h3 class="fw-bold"><?php echo e($orderCount); ?></h3></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-primary text-light"><i class="bi bi-currency-dollar"></i> Total Penjualan</div>
                <div class="card-body"><h3 class="fw-bold">Rp <?php echo e(number_format($totalSales, 0, ',', '.')); ?></h3></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-primary text-light"><i class="bi bi-graph-up"></i> Pertumbuhan</div>
                <div class="card-body">
                    <?php if(!is_null($growth)): ?>
                        <h3 class="fw-bold <?php echo e($growth >= 0 ? 'text-success' : 'text-danger'); ?>">
                            <?php echo e($growth >= 0 ? '+' : ''); ?><?php echo e($growth); ?>%
                        </h3>
                    <?php else: ?> <h3>-</h3> <?php endif; ?>
                </div>
            </div>
        </div>

    </div>


    <!-- ===== Sales Detail ===== -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-primary text-light"><i class="bi bi-arrow-counterclockwise"></i> Returned Products</div>
                <div class="card-body">
                    <p><strong>Return Rate:</strong> <?php echo e($returnRate); ?>%</p>
                    <?php if($returned->isEmpty()): ?>
                        <p class="text-muted">Tidak ada retur bulan ini.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php $__currentLoopData = $returned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?php echo e($r->productName); ?></span>
                                    <span><?php echo e($r->total_returned); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-primary text-light"><i class="bi bi-star"></i> Top 5 Produk Terlaris</div>
                <div class="card-body">
                    <?php if($topProducts->isEmpty()): ?>
                        <p class="text-muted">Belum ada penjualan bulan ini.</p>
                    <?php else: ?>
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="text-start" style="width: 75%;">Produk</th>
                                    <th class="text-center" style="width: 7%;">Qty</th>
                                    <th class="text-center" style="width: 18%;">Total</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($index >= 5 ? 'd-none extra-product' : ''); ?>">
                                        <td class="text-start"><?php echo e($tp->productName); ?></td>
                                        <td class="text-center"><?php echo e($tp->total_qty); ?></td>
                                        <td class="text-center">Rp <?php echo e(number_format($tp->total_sales, 0, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>

                        <?php if($topProducts->count() > 5): ?>
                            <button class="btn btn-sm btn-outline-primary mt-2" id="toggleTopProducts">Tampilkan Top 10</button>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>


    <!-- ===== Forecasting ===== -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-light shadow-sm mb-3">
                <div class="card-header bg-info text-dark">
                    <i class="bi bi-graph-up-arrow"></i> Prediksi Penjualan Bulan Depan (Top Produk)
                </div>
                <div class="card-body">
                    <?php if($forecastProducts->isEmpty()): ?>
                        <p class="text-muted">Belum ada data forecast untuk bulan depan.</p>
                    <?php else: ?>
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-center">Forecast Qty</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php $__currentLoopData = $forecastProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-start"><?php echo e($fp->productName); ?></td>
                                        <td class="text-center"><?php echo e($fp->forecast_quantity); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-info text-dark"><i class="bi bi-exclamation-triangle"></i> Produk Berisiko Shortage</div>
                <div class="card-body">
                    <?php if($shortageProducts->isEmpty()): ?>
                        <p class="text-muted">Tidak ada produk berisiko shortage bulan depan.</p>
                    <?php else: ?>
                        <div style="max-height:300px; overflow-y:auto;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light"><tr><th>Produk</th><th>Stok</th><th>Forecast</th></tr></thead>
                                <tbody>
                                    <?php $__currentLoopData = $shortageProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($sp->productName); ?></td>
                                            <td><?php echo e($sp->stock); ?></td>
                                            <td><?php echo e($sp->forecast_quantity); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Inventory ===== -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-dark shadow-sm mb-3">
                <div class="card-header bg-danger text-light">
                    <i class="bi bi-graph-up"></i> Margin Terbesar
                </div>
                <div class="card-body">
                    <?php if($inventoryTopMargin->isEmpty()): ?>
                        <p class="text-muted">Belum ada penjualan bulan ini.</p>
                    <?php else: ?>
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light small">
                                <tr>
                                    <th class="text-start">Produk</th>
                                    <th class="text-end">Total Margin</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php $__currentLoopData = $inventoryTopMargin; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-start"><?php echo e($itm->productName); ?></td>
                                        <td class="text-end">Rp <?php echo e(number_format($itm->total_margin, 0, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>

    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("toggleTopProducts");
        if (btn) {
            btn.addEventListener("click", () => {
                document.querySelectorAll(".extra-product").forEach(el => el.classList.toggle("d-none"));
                btn.textContent = btn.textContent.includes("10") ? "Tampilkan Top 5" : "Tampilkan Top 10";
            });
        }
    });


</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/Home/sales.blade.php ENDPATH**/ ?>