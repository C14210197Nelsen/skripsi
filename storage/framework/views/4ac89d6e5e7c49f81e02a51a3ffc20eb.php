

<?php $__env->startSection('title', 'Edit Rekapan'); ?>

<?php $__env->startSection('container'); ?>

<div class="card p-4 shadow rounded-3 col-md-6 mx-auto mt-4">
    <h4 class="mb-3">Edit Rekapan</h4>

    <form action="<?php echo e(route('rekapan.update', $rekapan->rekapanID)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="mb-3">
            <label for="tanggal" class="form-label">Date</label>
            <input type="date" name="tanggal" class="form-control"
                   value="<?php echo e(old('tanggal', $rekapan->tanggal)); ?>" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Description</label>
            <input type="text" name="deskripsi" class="form-control" maxlength="255"
                   value="<?php echo e(old('deskripsi', $rekapan->deskripsi)); ?>">
        </div>

        <div class="mb-3">
            <label for="tipe" class="form-label">Type</label>
            <select name="tipe" id="tipe" class="form-select" required onchange="filterKategori()">
                <option value="">-- Choose Type --</option>
                <option value="pemasukan" <?php echo e(old('tipe', $rekapan->tipe) == 'pemasukan' ? 'selected' : ''); ?>>Pemasukan</option>
                <option value="pengeluaran" <?php echo e(old('tipe', $rekapan->tipe) == 'pengeluaran' ? 'selected' : ''); ?>>Pengeluaran</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Category</label>
            <select name="kategori" id="kategori" class="form-select" required>
                <option value="">-- Choose Category --</option>
                
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Total</label>
            <input type="number" name="jumlah" class="form-control"
                   value="<?php echo e(old('jumlah', $rekapan->jumlah)); ?>" required>
        </div>

        <div class="mb-3">
            <label for="metode" class="form-label">Method</label>
            <input type="text" name="metode" class="form-control"
                   value="<?php echo e(old('metode', $rekapan->metode)); ?>">
        </div>

        <button type="submit" class="btn btn-danger px-4">Update</button>
        <a href="<?php echo e(route('rekapan.index')); ?>" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
  const pemasukan = <?php echo json_encode($pemasukanKategori, 15, 512) ?>;
  const pengeluaran = <?php echo json_encode($pengeluaranKategori, 15, 512) ?>;
  const oldKategori = <?php echo json_encode(old('kategori', $rekapan->kategori), 512) ?>;

  function filterKategori() {
    const tipe = document.getElementById('tipe').value;
    const kategoriSelect = document.getElementById('kategori');

    kategoriSelect.innerHTML = '<option value="">-- Choose Category --</option>';

    let kategoriList = [];
    if (tipe === 'pemasukan') kategoriList = pemasukan;
    if (tipe === 'pengeluaran') kategoriList = pengeluaran;

    kategoriList.forEach(function(kat) {
      const opt = document.createElement('option');
      opt.value = kat;
      opt.text = kat;
      if (kat === oldKategori) opt.selected = true;
      kategoriSelect.appendChild(opt);
    });
  }

  document.addEventListener('DOMContentLoaded', filterKategori);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/rekapan/edit.blade.php ENDPATH**/ ?>