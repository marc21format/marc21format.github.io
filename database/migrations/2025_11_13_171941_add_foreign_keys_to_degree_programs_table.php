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
        Schema::table('degree_programs', function (Blueprint $table) {
            $table->foreign(['degreefield_id'])->references(['degreefield_id'])->on('degree_fields')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['degreelevel_id'])->references(['degreelevel_id'])->on('degree_levels')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['degreetype_id'])->references(['degreetype_id'])->on('degree_types')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('degree_programs', function (Blueprint $table) {
            $table->dropForeign('degree_programs_degreefield_id_foreign');
            $table->dropForeign('degree_programs_degreelevel_id_foreign');
            $table->dropForeign('degree_programs_degreetype_id_foreign');
        });
    }
};
