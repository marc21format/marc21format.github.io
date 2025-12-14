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
        Schema::create('degree_level_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('degreelevel_id');
            $table->unsignedBigInteger('degreetype_id')->index('degree_level_type_degreetype_id_foreign');
            $table->timestamps();

            $table->unique(['degreelevel_id', 'degreetype_id'], 'dlt_level_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_level_type');
    }
};
