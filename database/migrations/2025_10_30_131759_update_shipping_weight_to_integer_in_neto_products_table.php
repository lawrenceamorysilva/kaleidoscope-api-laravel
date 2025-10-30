<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            // Change shipping_weight to integer (no decimals)
            $table->integer('shipping_weight')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('neto_products', function (Blueprint $table) {
            // Revert to original definition
            $table->decimal('shipping_weight', 8, 4)->nullable()->default(null)->change();
        });
    }
};
