<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNetoIdToNetoProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->unsignedBigInteger('neto_id')->nullable()->after('id')->index();
        });
    }

    public function down()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->dropColumn('neto_id');
        });
    }

}
