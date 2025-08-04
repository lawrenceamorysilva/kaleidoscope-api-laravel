<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToDropshipOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('dropship_order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->after('qty');
        });
    }

    public function down()
    {
        Schema::table('dropship_order_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

}
