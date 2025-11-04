<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFilenameUserIndexToDropshipOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->index(
                ['status', 'dropship_order_filename_id', 'user_id'],
                'dropship_orders_status_filename_user_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropIndex('dropship_orders_status_filename_user_idx');
        });
    }
}
