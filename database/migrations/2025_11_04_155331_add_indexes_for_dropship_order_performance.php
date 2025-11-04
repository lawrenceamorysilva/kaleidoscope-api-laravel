<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForDropshipOrderPerformance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // For filtering active users
            $table->index('active', 'users_active_idx');
        });

        Schema::table('dropship_orders', function (Blueprint $table) {
            // For combined status/null/user lookups
            $table->index(
                ['status', 'dropship_order_filename_id', 'user_id'],
                'dropship_orders_status_filename_user_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_active_idx');
        });

        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropIndex('dropship_orders_status_filename_user_idx');
        });
    }
}
