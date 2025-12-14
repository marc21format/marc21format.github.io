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
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->foreign(['subject_id'])->references(['subject_id'])->on('volunteer_subjects')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_teachers', function (Blueprint $table) {
            $table->dropForeign('subject_teachers_subject_id_foreign');
            $table->dropForeign('subject_teachers_user_id_foreign');
        });
    }
};
