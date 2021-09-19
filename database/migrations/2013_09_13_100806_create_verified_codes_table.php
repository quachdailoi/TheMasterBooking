<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifiedCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verified_codes', function (Blueprint $table) {
            $table->id();
            $table->string('receiver')->comment('email and phone');
            $table->string('code');
            $table->unsignedTinyInteger('type')->default(0)->comment('0: register, 1: reset password');
            $table->unsignedTinyInteger('channel')->default(0)->comment('0: email, 1: phone');
            $table->boolean('wasVerified')->default(false)->comment('check code was verified or not');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verified_codes', function (Blueprint $table) {
            //
        });
    }
}
