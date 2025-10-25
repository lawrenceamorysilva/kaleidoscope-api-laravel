<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentReferenceToDropshipOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            // Add after grand_total for logical grouping
            $table->string('payment_reference', 255)
                ->nullable()
                ->after('grand_total')
                ->collation('utf8mb4_unicode_ci');
        });
    }

    public function down()
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
}
