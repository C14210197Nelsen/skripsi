<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('salesorder', function (Blueprint $table) {
            $table->unsignedBigInteger('salesID')->autoIncrement()->primary();
            $table->date('salesDate');
            $table->unsignedBigInteger('Customer_customerID');
            $table->tinyInteger('status')->default(1);
            $table->integer('totalPrice')->default(0);
            $table->integer('totalHPP')->default(0);
            $table->integer('totalProfit')->default(0);
            $table->timestamps();

            // Foreign key ke customer
            $table->foreign('Customer_customerID')
                  ->references('customerID')
                  ->on('customer')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salesorder');
    }
};
