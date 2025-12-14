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
        Schema::create('subject_teachers', function (Blueprint $table) {
            $table->bigIncrements('teacher_id');
            $table->unsignedBigInteger('user_id')->index('subject_teachers_user_id_foreign');
            $table->unsignedBigInteger('subject_id')->index('subject_teachers_subject_id_foreign');
            $table->enum('subject_proficiency', ['beginner', 'competent', 'proficient'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_teachers');
    }
};
