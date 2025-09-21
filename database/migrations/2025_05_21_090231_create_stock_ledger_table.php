<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockLedgerTable extends Migration
{
    public function up()
    {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id('stockledgerID');
            
            // Relasi ke produk
            $table->unsignedBigInteger('productID');
            $table->foreign('productID')->references('productID')->on('product')->onDelete('cascade');
            
            // Kolom-kolom transaksi
            $table->integer('qty');
            $table->integer('saldo_qty')->nullable();
            $table->decimal('saldo_harga', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
            $table->decimal('hpp', 15, 2)->nullable();

            // Metadata transaksi
            $table->enum('type', ['in', 'out']);
            $table->string('source_type'); // contoh: 'PO', 'SO', 'ADJUSTMENT'
            $table->unsignedBigInteger('source_id');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_ledger');
    }
}
