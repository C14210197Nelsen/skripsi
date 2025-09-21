<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchasedetail', function (Blueprint $table) {
            $table->unsignedBigInteger('purchasedetailID')->autoIncrement()->primary();

            $table->unsignedBigInteger('PurchaseOrder_purchaseID');
            $table->unsignedBigInteger('Product_productID');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('subtotal');
            $table->tinyInteger('status')->default(1);


            // Foreign key ke tabel purchaseorder
            $table->foreign('PurchaseOrder_purchaseID')
                  ->references('purchaseID')
                  ->on('purchaseorder')
                  ->onDelete('cascade');

            // Foreign key ke tabel product
            $table->foreign('Product_productID')
                  ->references('productID')
                  ->on('product')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchasedetail');
    }
};
