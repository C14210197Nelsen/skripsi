<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userlogin', function (Blueprint $table) {
            $table->unsignedBigInteger('userID')->autoIncrement()->primary();
            $table->string('username', 32)->unique();
            $table->string('password', 255);
            $table->string('fullName', 64);
            $table->enum('role', ['Owner', 'Purchase', 'Sales']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userlogin');
    }
};
