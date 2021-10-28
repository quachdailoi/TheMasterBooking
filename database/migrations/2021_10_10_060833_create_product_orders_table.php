<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->double('amount')->default(0);
            $table->dateTime('order_date');
            $table->unsignedTinyInteger('status')->default(1);
            $table->text('address');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('receiver_name');
            $table->string('shipping_method');
            $table->string('payment_method');
            $table->json('products');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_orders');
    }
}
