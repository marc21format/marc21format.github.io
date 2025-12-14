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
        Schema::create('degree_programs', function (Blueprint $table) {
            $table->bigIncrements('degreeprogram_id');
            $table->string('full_degree_program_name');
            $table->unsignedBigInteger('degreelevel_id')->nullable()->index('degree_programs_degreelevel_id_foreign');
            $table->unsignedBigInteger('degreetype_id')->nullable()->index('degree_programs_degreetype_id_foreign');
            $table->unsignedBigInteger('degreefield_id')->nullable()->index('degree_programs_degreefield_id_foreign');
            $table->timestamps();
            $table->string('program_abbreviation', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_programs');
    }
};
