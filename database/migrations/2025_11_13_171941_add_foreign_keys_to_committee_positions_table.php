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
        Schema::table('committee_positions', function (Blueprint $table) {
            $table->foreign(['committee_id'], 'committees')->references(['committee_id'])->on('committees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['position_id'], 'positions')->references(['position_id'])->on('positions')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_positions', function (Blueprint $table) {
            $table->dropForeign('committees');
            $table->dropForeign('positions');
        });
    }
};
