<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToSomeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_shifts', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('user_skills', function (Blueprint $table) {
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
        Schema::table('some', function (Blueprint $table) {
            //
        });
    }
}
