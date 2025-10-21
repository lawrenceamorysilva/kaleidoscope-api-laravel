<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            // Add 'state' column right after 'suburb'
            $table->string('state', 100)
                ->nullable()
                ->after('suburb')
                ->collation('utf8mb4_unicode_ci');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_costs', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
