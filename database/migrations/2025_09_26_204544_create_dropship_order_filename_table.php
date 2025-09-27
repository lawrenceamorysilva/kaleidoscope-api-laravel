<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropshipOrderFilenameTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dropship_order_filename', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_users_id');
            $table->string('filename');
            $table->unsignedInteger('dl_counter')->default(0);
            $table->timestamps(); // includes created_at + updated_at

            $table->timestamp('dl_date')->nullable();

            // FK to admin_users table
            $table->foreign('admin_users_id')
                ->references('id')
                ->on('admin_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dropship_order_filename');
    }
}
