<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            // Already exists: idx_neto_products_dropship
            // Add missing indexes
            if (! $this->indexExists('neto_products', 'idx_neto_products_stock_status')) {
                $table->index('stock_status', 'idx_neto_products_stock_status');
            }

            if (! $this->indexExists('neto_products', 'idx_neto_products_dropship_stock_status')) {
                $table->index(['dropship', 'stock_status'], 'idx_neto_products_dropship_stock_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            $table->dropIndex('idx_neto_products_stock_status');
            $table->dropIndex('idx_neto_products_dropship_stock_status');
        });
    }

    // Helper to check index existence
    private function indexExists(string $table, string $index): bool
    {
        return Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails($table)
            ->hasIndex($index);
    }
};
