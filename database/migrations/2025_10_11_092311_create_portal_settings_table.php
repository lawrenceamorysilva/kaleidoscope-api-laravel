<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. minimum_order_fee
            $table->string('value');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_settings');
    }
};
