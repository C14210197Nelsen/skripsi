<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturndetailTable extends Migration
{
    public function up(): void
    {
        Schema::create('returndetail', function (Blueprint $table) {
            $table->id('returndetailID');
            $table->unsignedBigInteger('returnID');
            $table->unsignedBigInteger('productID');
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->foreign('returnID')->references('returnID')->on('returnorder')->onDelete('cascade');
            // Anda bisa tambahkan foreign key ke tabel produk jika diperlukan:
            // $table->foreign('productID')->references('productID')->on('product')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returndetail');
    }
}
