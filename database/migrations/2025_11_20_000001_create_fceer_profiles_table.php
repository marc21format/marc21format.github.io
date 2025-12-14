<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('fceer_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('volunteer_number', 45)->nullable();
            $table->string('student_number', 45)->nullable();
            $table->year('fceer_batch');
            $table->unsignedBigInteger('student_group')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_group')->references('room_id')->on('rooms')->onDelete('set null');
        });
    }
    public function down()
    {
        Schema::dropIfExists('fceer_profiles');
    }
};
