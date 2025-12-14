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
        Schema::table('highschool_records', function (Blueprint $table) {
            $table->foreign(['highschool_id'])->references(['highschool_id'])->on('highschools')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('highschool_records', function (Blueprint $table) {
            $table->dropForeign('highschool_records_highschool_id_foreign');
            $table->dropForeign('highschool_records_user_id_foreign');
        });
    }
};
