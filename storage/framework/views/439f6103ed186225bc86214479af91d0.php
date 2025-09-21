

<?php $__env->startSection('title', 'Rekapan Bulanan'); ?>

<?php $__env->startSection('container'); ?>
<div class="container">

  
  <?php if(session('success')): ?>
    <div class="alert alert-success shadow-sm rounded"><?php echo e(session('success')); ?></div>
  <?php endif; ?>

  
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <form action="<?php echo e(route('rekapan.index')); ?>" method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
              <label class="form-label mb-1">Filter Bulan</label>
              <input type="text" name="tanggal" 
                  class="form-control form-control-sm rounded-pill monthpicker"
                  value="<?php echo e(request('tanggal', date('Y-m'))); ?>">
        </div>

        <div class="col-md-5 d-flex gap-2">
          <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Go</button>
          <?php if(request()->has('tanggal')): ?>
            <a href="<?php echo e(route('rekapan.index')); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
          <?php endif; ?>
        </div>

        <div class="col-md-3 text-end">
          <a href="<?php echo e(route('rekapan.create')); ?>" class="btn btn-danger rounded-pill px-4">+ Create</a>
        </div>
      </form>
    </div>
  </div>


  <div class="row g-3">
    <div class="col-md-6">
      
      <h5 class="fw-bold text-success">Pemasukan</h5>
      <table class="table table-bordered text-center align-middle">
        <thead class="table-success">
          <tr>
            <th width="20%">Tanggal</th>
            <th width="40%">Keterangan</th>
            <th width="20%">Total</th>
            <th width="20%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr data-bs-toggle="collapse" data-bs-target="#salesDetail" class="cursor-pointer">
            <td><?php echo e(\Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y')); ?></td>
            <td class="text-start">Pemasukan dari Penjualan</td>
            <td>Rp <?php echo e(number_format($totalSales, 0, ',', '.')); ?></td>
            <td><span class="text-primary">Lihat Detail</span></td>
          </tr>
          <tr>
            <td colspan="4" class="p-0">
              <div class="collapse" id="salesDetail">
                <table class="table table-sm table-striped table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Sales ID</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__currentLoopData = $salesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($s->salesDate)->format('d-m-Y')); ?></td>
                        <td>Sales #<?php echo e($s->salesID); ?></td>
                        <td>Rp <?php echo e(number_format($s->totalPrice, 0, ',', '.')); ?></td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          
          <?php $__currentLoopData = $rekapanManual->where('tipe', 'pemasukan'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e(\Carbon\Carbon::parse($r->tanggal)->format('d-m-Y')); ?></td>
            <td class="text-start"><?php echo e($r->deskripsi); ?></td>
            <td>Rp <?php echo e(number_format($r->jumlah, 0, ',', '.')); ?></td>
            <td class="text-nowrap">
              <a href="<?php echo e(route('rekapan.edit', $r->rekapanID)); ?>"
                class="btn btn-sm btn-outline-warning btn-icon" title="Edit">
                ✏️
              </a>

              <form action="<?php echo e(route('rekapan.destroy', $r->rekapanID)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"
                        onclick="return confirm('Hapus data ini?')">
                  🗑️
                </button>
              </form>
            </td>

          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>

    <div class="col-md-6">
      
      <h5 class="fw-bold text-danger">Pengeluaran</h5>
      <table class="table table-bordered text-center align-middle">
        <thead class="table-danger">
          <tr>
            <th width="20%">Tanggal</th>
            <th width="40%">Keterangan</th>
            <th width="20%">Total</th>
            <th width="20%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr data-bs-toggle="collapse" data-bs-target="#purchaseDetail" class="cursor-pointer">
            <td><?php echo e(\Carbon\Carbon::parse($bulan . '-01')->translatedFormat('F Y')); ?></td>
            <td class="text-start">Pengeluaran dari Pembelian</td>
            <td>Rp <?php echo e(number_format($totalPurchase, 0, ',', '.')); ?></td>
            <td><span class="text-primary">Lihat Detail</span></td>
          </tr>
          <tr>
            <td colspan="4" class="p-0">
              <div class="collapse" id="purchaseDetail">
                <table class="table table-sm table-striped table-bordered mb-0">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Purchase ID</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $__currentLoopData = $purchaseData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <tr>
                        <td><?php echo e(\Carbon\Carbon::parse($s->purchaseDate)->format('d-m-Y')); ?></td>
                        <td>Purchase #<?php echo e($s->purchaseID); ?></td>
                        <td>Rp <?php echo e(number_format($s->totalPrice, 0, ',', '.')); ?></td>
                      </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>

          
          <?php $__currentLoopData = $rekapanManual->where('tipe', 'pengeluaran'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td><?php echo e(\Carbon\Carbon::parse($r->tanggal)->format('d-m-Y')); ?></td>
            <td class="text-start"><?php echo e($r->deskripsi); ?></td>
            <td>Rp <?php echo e(number_format($r->jumlah, 0, ',', '.')); ?></td>
            <td class="text-nowrap">
              <a href="<?php echo e(route('rekapan.edit', $r->rekapanID)); ?>"
                class="btn btn-sm btn-outline-warning btn-icon" title="Edit">
                ✏️
              </a>

              <form action="<?php echo e(route('rekapan.destroy', $r->rekapanID)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus"
                        onclick="return confirm('Hapus data ini?')">
                  🗑️
                </button>
              </form>
            </td>

          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>

  
  

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Datepicker CSS & JS -->
<link rel="stylesheet" 
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>

<!-- Aktifkan Monthpicker -->
<script>
$(function() {
    $('.monthpicker').datepicker({
        format: "yyyy-mm",      // hasil: 2025-08
        startView: "months",    // buka langsung mode bulan
        minViewMode: "months",  // hanya bulan & tahun
        autoclose: true,        // otomatis close setelah pilih
        todayHighlight: true
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/rekapan/index.blade.php ENDPATH**/ ?>