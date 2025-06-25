<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSurchargeToNetoProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->decimal('surcharge', 8, 2)->nullable()->after('dropship_price');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->dropColumn('surcharge');
        });
    }
}
