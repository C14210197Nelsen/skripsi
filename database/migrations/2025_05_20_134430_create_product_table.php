<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->unsignedBigInteger('productID')->autoIncrement()->primary();

            $table->string('productCode', 8)->unique();
            $table->string('productName', 64);
            $table->integer('productPrice');
            $table->integer('productCost');
            $table->string('productType', 8);
            $table->integer('stock')->default(0)->nullable();
            $table->integer('minStock')->nullable();
            $table->boolean('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
