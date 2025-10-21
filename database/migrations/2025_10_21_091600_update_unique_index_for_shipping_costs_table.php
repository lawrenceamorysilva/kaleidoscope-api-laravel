<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->dropUnique('idx_courier_postcode_weight'); // old index
            $table->unique(['courier', 'state', 'suburb', 'postcode', 'weight_kg'], 'idx_courier_state_postcode_weight');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->dropUnique('idx_courier_state_postcode_weight');
            $table->unique(['courier', 'suburb', 'postcode', 'weight_kg'], 'idx_courier_postcode_weight');
        });
    }
};
