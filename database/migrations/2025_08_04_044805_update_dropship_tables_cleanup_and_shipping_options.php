<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Clean up dropship_order_items table
        Schema::table('dropship_order_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'subtotal', 'shipping_weight']);
        });

        // Add available_shipping_options to dropship_orders
        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->json('available_shipping_options')->nullable()->after('selected_courier');
        });
    }

    public function down(): void
    {
        // Reverse changes if needed
        Schema::table('dropship_order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('shipping_weight', 10, 2)->nullable();
        });

        Schema::table('dropship_orders', function (Blueprint $table) {
            $table->dropColumn('available_shipping_options');
        });
    }
};
