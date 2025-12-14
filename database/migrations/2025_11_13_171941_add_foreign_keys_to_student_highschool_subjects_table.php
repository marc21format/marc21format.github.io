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
        Schema::table('student_highschool_subjects', function (Blueprint $table) {
            $table->foreign(['highschoolsubject_id'])->references(['highschoolsubject_id'])->on('highschool_subjects')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_highschool_subjects', function (Blueprint $table) {
            $table->dropForeign('student_highschool_subjects_highschoolsubject_id_foreign');
            $table->dropForeign('student_highschool_subjects_user_id_foreign');
        });
    }
};
