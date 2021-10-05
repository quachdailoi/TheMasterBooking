<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->text('path')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            $table->tinyInteger('status')->default(1)->comment('1: active, 0: inactive');
            $table->tinyInteger('type')->default(0)->comment('0: image', '1: video', '2: others');
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
        Schema::dropIfExists('files');
    }
}
