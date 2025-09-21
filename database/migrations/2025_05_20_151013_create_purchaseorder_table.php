<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchaseorder', function (Blueprint $table) {
            $table->unsignedBigInteger('purchaseID')->autoIncrement()->primary();
            $table->date('purchaseDate');
            $table->unsignedBigInteger('Supplier_supplierID');
            $table->tinyInteger('status')->default(1);
            $table->integer('totalPrice')->default(0);
            $table->timestamps();

            // Foreign key ke supplier
            $table->foreign('Supplier_supplierID')
                  ->references('supplierID')
                  ->on('supplier')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchaseorder');
    }
};
