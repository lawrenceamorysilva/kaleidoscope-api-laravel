<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexesForDropshipOrderPerformance extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Users table
        $usersIndexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'users')
            ->where('index_name', 'users_active_idx')
            ->exists();

        if (!$usersIndexExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('active', 'users_active_idx');
            });
        }

        // Dropship orders table
        $dropshipIndexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'dropship_orders')
            ->where('index_name', 'dropship_orders_status_filename_user_idx')
            ->exists();

        if (!$dropshipIndexExists) {
            Schema::table('dropship_orders', function (Blueprint $table) {
                $table->index(
                    ['status', 'dropship_order_filename_id', 'user_id'],
                    'dropship_orders_status_filename_user_idx'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
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
