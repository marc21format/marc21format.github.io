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
        Schema::create('degree_field_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('degreefield_id');
            $table->unsignedBigInteger('degreelevel_id')->index('degree_field_mappings_degreelevel_id_foreign');
            $table->unsignedBigInteger('degreetype_id')->nullable()->index('degree_field_mappings_degreetype_id_foreign');
            $table->timestamps();

            $table->unique(['degreefield_id', 'degreelevel_id', 'degreetype_id'], 'dfm_unique_field_level_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_field_mappings');
    }
};
