<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnorderTable extends Migration
{
    public function up(): void
    {
        Schema::create('returnorder', function (Blueprint $table) {
            $table->id('returnID');
            $table->date('returnDate');
            $table->enum('type', ['sales', 'purchase']);
            $table->unsignedBigInteger('sourceID');
            $table->unsignedBigInteger('partnerID');
            $table->tinyInteger('status')->default(1); // 1 = aktif, 0 = batal, 2 = selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returnorder');
    }
}
