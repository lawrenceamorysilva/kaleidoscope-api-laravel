<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->enum('type', ['string', 'number', 'boolean', 'json'])
                ->default('string')
                ->after('value');
        });

        // ✅ Backfill known numeric keys
        DB::table('portal_settings')
            ->whereIn('key', [
                'minimum_order_value',
                'dropship_fee',
                'minimum_freight_charge',
            ])
            ->update(['type' => 'number']);
    }

    public function down(): void
    {
        Schema::table('portal_settings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
