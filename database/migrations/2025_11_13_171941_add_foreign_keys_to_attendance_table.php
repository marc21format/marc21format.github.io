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
        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign(['letter_id'], 'attendance_letter_fk')->references(['letter_id'])->on('student_excuse_letters')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['letter_id'])->references(['letter_id'])->on('student_excuse_letters')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['recorded_by'], 'attendance_user_id_foreign_key_recorded_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['updated_by'], 'attendance_user_id_foreign_key_updated_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign('attendance_letter_fk');
            $table->dropForeign('attendance_letter_id_foreign');
            $table->dropForeign('attendance_user_id_foreign');
            $table->dropForeign('attendance_user_id_foreign_key_recorded_by');
            $table->dropForeign('attendance_user_id_foreign_key_updated_by');
        });
    }
};
