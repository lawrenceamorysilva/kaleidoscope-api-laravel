<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            // Composite index for export filtering
            $table->index(
                ['status', 'dropship_order_filename_id'],
                'dropship_status_filename_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropIndex('dropship_status_filename_idx');
        });
    }
};
