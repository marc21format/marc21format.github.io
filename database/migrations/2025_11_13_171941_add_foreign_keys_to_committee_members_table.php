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
        Schema::table('committee_members', function (Blueprint $table) {
            $table->foreign(['committee_id'])->references(['committee_id'])->on('committees')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['position_id'])->references(['position_id'])->on('positions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_members', function (Blueprint $table) {
            $table->dropForeign('committee_members_committee_id_foreign');
            $table->dropForeign('committee_members_position_id_foreign');
            $table->dropForeign('committee_members_user_id_foreign');
        });
    }
};
