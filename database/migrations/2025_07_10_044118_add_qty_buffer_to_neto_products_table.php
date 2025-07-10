<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddQtyBufferToNetoProductsTable extends Migration
{
    public function up()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->integer('qty_buffer')->default(0)->after('qty');
        });
    }

    public function down()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->dropColumn('qty_buffer');
        });
    }
}

