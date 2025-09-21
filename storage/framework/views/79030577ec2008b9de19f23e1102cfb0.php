

<?php $__env->startSection('title', 'Laporan Laba Rugi'); ?>

<?php $__env->startSection('container'); ?>
<form method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-4">
                <label class="form-label mb-1">Filter Bulan</label>
                <input type="text" name="tanggal" 
                    class="form-control form-control-sm rounded-pill monthpicker"
                    value="<?php echo e(request('tanggal', date('Y-m'))); ?>">

        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">Go</button>
        </div>
    </div>
</form>


<h4><strong>Laporan Laba Rugi Bulan <?php echo e($bulan); ?>/<?php echo e($tahun); ?></strong></h4>

<table class="table table-bordered">
    <tr><th class="text-start">Pendapatan</th><td class="text-success">Rp<?php echo e(number_format($pendapatan)); ?></td></tr>
    <tr><th class="text-start">Harga Pokok Penjualan</th><td class="text-danger">- Rp<?php echo e(number_format($total_hpp)); ?></td></tr>
    <tr class="table-warning"><th><strong>Laba Kotor</strong></th><td><strong>Rp<?php echo e(number_format($laba_kotor)); ?></strong></td></tr>
    <tr><th class="text-start">Pemasukan Lain</th><td class="text-success">Rp<?php echo e(number_format($pemasukan_lain)); ?></td></tr>
    <tr><td colspan="2" class="text-start">
        <ul class="mb-2">
            <?php $__currentLoopData = $pemasukan_per_kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($item->kategori); ?>: Rp<?php echo e(number_format($item->total)); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </td></tr>

    <tr><th class="text-start">Pengeluaran</th><td class="text-danger">- Rp<?php echo e(number_format($pengeluaran)); ?></td></tr>
    <tr><td colspan="2" class="text-start">
        <ul class="mb-2">
            <?php $__currentLoopData = $pengeluaran_per_kategori; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($item->kategori); ?>: Rp<?php echo e(number_format($item->total)); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </td></tr>

    <tr class="table-success">
        <th><strong>Laba Bersih</strong></th><td><strong>Rp<?php echo e(number_format($laba_bersih)); ?></strong></td>
    </tr>
</table>


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

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/laporan/index.blade.php ENDPATH**/ ?>