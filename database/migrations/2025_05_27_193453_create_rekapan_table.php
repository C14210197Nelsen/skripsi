<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRekapanTable extends Migration
{
    public function up()
    {
        Schema::create('rekapan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');                      // Tanggal transaksi
            $table->string('kategori');                   // Misal: Penjualan, Pembelian, Gaji, dll.
            $table->enum('tipe', ['pemasukan', 'pengeluaran']); // Jenis transaksi
            $table->integer('jumlah');             // Jumlah nominal
            $table->string('metode')->nullable();         // Cash, Transfer, QRIS, dll. (opsional)
            $table->string('deskripsi', 255)->nullable();      // Keterangan tambahan
            $table->timestamps();                         // created_at & updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('rekapan');
    }
}
