<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropshipOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropship_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dropship_order_id')->constrained()->onDelete('cascade');
            $table->string('sku');
            $table->string('name');
            $table->decimal('unit_price', 10, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 10, 2); // unit_price * qty
            $table->decimal('shipping_weight', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dropship_order_items');
    }
}
