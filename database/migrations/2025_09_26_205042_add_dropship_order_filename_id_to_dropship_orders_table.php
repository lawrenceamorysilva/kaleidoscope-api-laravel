<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDropshipOrderFilenameIdToDropshipOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('dropship_order_filename_id')->nullable()->after('id');

            $table->foreign('dropship_order_filename_id')
                ->references('id')
                ->on('dropship_order_filename')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropForeign(['dropship_order_filename_id']);
            $table->dropColumn('dropship_order_filename_id');
        });
    }
}
