<?php

// database/migrations/xxxx_xx_xx_create_neto_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetoProductsTable extends Migration
{
    public function up()
    {
        Schema::create('neto_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->boolean('approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('stock_status')->nullable(); // Misc15
            $table->string('dropship')->nullable(); // Misc24
            $table->decimal('dropship_price', 10, 2)->nullable(); // Misc11
            $table->integer('qty')->nullable();
            $table->decimal('shipping_weight', 8, 4)->nullable();
            $table->decimal('shipping_length', 8, 3)->nullable();
            $table->decimal('shipping_width', 8, 3)->nullable();
            $table->decimal('shipping_height', 8, 3)->nullable();
            $table->json('images')->nullable(); // Will store all images as JSON
            $table->string('status')->default('active'); // active, inactive
            $table->text('status_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('neto_products');
    }
}

