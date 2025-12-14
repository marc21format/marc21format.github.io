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
        Schema::create('degree_levels', function (Blueprint $table) {
            $table->bigIncrements('degreelevel_id');
            $table->string('level_name');
            $table->timestamps();
            $table->string('degree_level', 45);
            $table->string('abbreviation', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_levels');
    }
};
