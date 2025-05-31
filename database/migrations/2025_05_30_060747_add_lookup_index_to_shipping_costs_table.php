<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLookupIndexToShippingCostsTable extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->index(['postcode', 'suburb', 'weight_kg'], 'idx_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->dropIndex('idx_lookup');
        });
    }
}
