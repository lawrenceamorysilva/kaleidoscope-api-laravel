<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            // For faster JOIN on filename reference
            $table->index('dropship_order_filename_id', 'dropship_filename_idx');
        });
    }

    public function down(): void
    {
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropIndex('dropship_filename_idx');
        });
    }
};
