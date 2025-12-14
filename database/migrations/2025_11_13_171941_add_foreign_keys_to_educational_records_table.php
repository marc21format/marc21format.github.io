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
        Schema::table('educational_records', function (Blueprint $table) {
            $table->foreign(['degreeprogram_id'], 'degreeprogram_id_foreign')->references(['degreeprogram_id'])->on('degree_programs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['university_id'], 'university_id_foreign')->references(['university_id'])->on('universities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'], 'users_id_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educational_records', function (Blueprint $table) {
            $table->dropForeign('degreeprogram_id_foreign');
            $table->dropForeign('university_id_foreign');
            $table->dropForeign('users_id_foreign');
        });
    }
};
