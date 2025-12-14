<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('room_id');
            $table->string('group', 255);
            $table->unsignedBigInteger('adviser_id')->nullable();
            $table->unsignedBigInteger('president_id')->nullable();
            $table->unsignedBigInteger('secretary_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('adviser_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('president_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('secretary_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};
