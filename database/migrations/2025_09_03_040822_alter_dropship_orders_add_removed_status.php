<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE dropship_orders
            MODIFY COLUMN status
            ENUM('open','for_shipping','fulfilled','canceled','removed')
            NOT NULL DEFAULT 'open'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE dropship_orders
            MODIFY COLUMN status
            ENUM('open','for_shipping','fulfilled','canceled')
            NOT NULL DEFAULT 'open'
        ");
    }
};
