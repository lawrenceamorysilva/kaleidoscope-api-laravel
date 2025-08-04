<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDropshipOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropship_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // who submitted the order
            $table->string('po_number')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('business_name')->nullable();
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('suburb');
            $table->string('state');
            $table->string('postcode');
            $table->string('phone')->nullable();
            $table->boolean('authority_to_leave')->default(false);
            $table->decimal('product_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('dropship_fee', 10, 2)->default(0);
            $table->decimal('min_order_fee', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('selected_courier')->nullable();
            $table->enum('status', ['open', 'for_shipping', 'fulfilled','canceled'])->default('open');
            $table->string('status_reason')->nullable();
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
        Schema::dropIfExists('dropship_orders');
    }
}
