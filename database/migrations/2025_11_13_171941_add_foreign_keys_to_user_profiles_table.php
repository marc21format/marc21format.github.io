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
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->foreign(['student_group'], 'student_room')->references(['room_id'])->on('rooms')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['address_id'])->references(['address_id'])->on('addresses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'])->references(['status_id'])->on('user_attendance_status')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropForeign('student_room');
            $table->dropForeign('user_profiles_address_id_foreign');
            $table->dropForeign('user_profiles_status_id_foreign');
            $table->dropForeign('user_profiles_user_id_foreign');
        });
    }
};
