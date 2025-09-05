<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            // Add index on dropship
            $table->index('dropship', 'idx_neto_products_dropship');
        });
    }

    public function down(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->dropIndex('idx_neto_products_dropship');
        });
    }
};
