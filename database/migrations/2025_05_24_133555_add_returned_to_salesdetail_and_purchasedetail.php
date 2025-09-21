<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReturnedToSalesdetailAndPurchasedetail extends Migration
{
    public function up()
    {
        Schema::table('salesdetail', function (Blueprint $table) {
            $table->integer('returned')->default(0)->after('quantity');
        });

        Schema::table('purchasedetail', function (Blueprint $table) {
            $table->integer('returned')->default(0)->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('salesdetail', function (Blueprint $table) {
            $table->dropColumn('returned');
        });

        Schema::table('purchasedetail', function (Blueprint $table) {
            $table->dropColumn('returned');
        });
    }
}
