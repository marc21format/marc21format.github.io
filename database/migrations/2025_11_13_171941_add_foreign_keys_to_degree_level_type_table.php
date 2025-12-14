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
        Schema::table('degree_level_type', function (Blueprint $table) {
            $table->foreign(['degreelevel_id'])->references(['degreelevel_id'])->on('degree_levels')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['degreetype_id'])->references(['degreetype_id'])->on('degree_types')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degree_level_type', function (Blueprint $table) {
            $table->dropForeign('degree_level_type_degreelevel_id_foreign');
            $table->dropForeign('degree_level_type_degreetype_id_foreign');
        });
    }
};
