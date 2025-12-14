<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->bigIncrements('userprofile_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('f_name', 45);
            $table->string('m_name', 45)->nullable();
            $table->string('s_name', 45);
            $table->string('generational_suffix', 10)->nullable();
            $table->string('phone_number', 15)->nullable()->unique();
            $table->date('birthday')->nullable();
            $table->enum('sex', ['Female', 'Male'])->nullable();
            $table->unsignedBigInteger('address_id')->nullable()->index('user_profiles_address_id_foreign');
            $table->unsignedBigInteger('status_id')->nullable()->index('user_profiles_status_id_foreign');
            $table->string('student_number', 15)->nullable();
            $table->unsignedBigInteger('student_group')->nullable()->index('student_room_idx');
            $table->string('volunteer_number', 15)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->year('batch')->nullable()->index('batch_id_foreign_idx');
            $table->string('lived_name', 45)->nullable();
            $table->string('age', 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
