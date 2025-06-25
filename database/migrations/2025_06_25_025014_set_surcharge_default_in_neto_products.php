<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::table('neto_products')->whereNull('surcharge')->update(['surcharge' => 0.00]);
        DB::statement("ALTER TABLE neto_products MODIFY surcharge DECIMAL(8,2) NOT NULL DEFAULT 0.00");
    }

    public function down()
    {
        DB::statement("ALTER TABLE neto_products MODIFY surcharge DECIMAL(8,2) NULL");
    }

};
