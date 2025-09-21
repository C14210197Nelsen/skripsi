<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salesdetail', function (Blueprint $table) {
            $table->unsignedBigInteger('salesdetailID')->autoIncrement()->primary();

            $table->unsignedBigInteger('SalesOrder_salesID');
            $table->unsignedBigInteger('Product_productID');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('subtotal');
            $table->integer('cost');


            // Foreign keys
            $table->foreign('SalesOrder_salesID')
                  ->references('salesID')
                  ->on('salesorder')
                  ->onDelete('cascade');

            $table->foreign('Product_productID')
                  ->references('productID')
                  ->on('product')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesdetail');
    }
};
