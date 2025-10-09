<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dropship_order_filename', function (Blueprint $table) {
            // Speeds up ORDER BY created_at DESC for export history
            $table->index('created_at', 'dropship_filename_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('dropship_order_filename', function (Blueprint $table) {
            $table->dropIndex('dropship_filename_created_idx');
        });
    }
};
