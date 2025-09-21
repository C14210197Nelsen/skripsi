@extends('layouts.main')

@section('title', 'Edit Rekapan')

@section('container')

<div class="card p-4 shadow rounded-3 col-md-6 mx-auto mt-4">
    <h4 class="mb-3">Edit Rekapan</h4>

    <form action="{{ route('rekapan.update', $rekapan->rekapanID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="tanggal" class="form-label">Date</label>
            <input type="date" name="tanggal" class="form-control"
                   value="{{ old('tanggal', $rekapan->tanggal) }}" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Description</label>
            <input type="text" name="deskripsi" class="form-control" maxlength="255"
                   value="{{ old('deskripsi', $rekapan->deskripsi) }}">
        </div>

        <div class="mb-3">
            <label for="tipe" class="form-label">Type</label>
            <select name="tipe" id="tipe" class="form-select" required onchange="filterKategori()">
                <option value="">-- Choose Type --</option>
                <option value="pemasukan" {{ old('tipe', $rekapan->tipe) == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ old('tipe', $rekapan->tipe) == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Category</label>
            <select name="kategori" id="kategori" class="form-select" required>
                <option value="">-- Choose Category --</option>
                {{-- JS --}}
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Total</label>
            <input type="number" name="jumlah" class="form-control"
                   value="{{ old('jumlah', $rekapan->jumlah) }}" required>
        </div>

        <div class="mb-3">
            <label for="metode" class="form-label">Method</label>
            <input type="text" name="metode" class="form-control"
                   value="{{ old('metode', $rekapan->metode) }}">
        </div>

        <button type="submit" class="btn btn-danger px-4">Update</button>
        <a href="{{ route('rekapan.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>

@endsection

@section('scripts')
<script>
  const pemasukan = @json($pemasukanKategori);
  const pengeluaran = @json($pengeluaranKategori);
  const oldKategori = @json(old('kategori', $rekapan->kategori));

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
@endsection
