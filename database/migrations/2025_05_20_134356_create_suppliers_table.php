<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->unsignedBigInteger('supplierID')->autoIncrement()->primary();

            $table->string('supplierName', 32);
            $table->string('address', 4000)->nullable();
            $table->string('telephone', 16)->nullable();
            $table->boolean('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
