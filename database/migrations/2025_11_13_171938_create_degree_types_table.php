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
        Schema::create('degree_types', function (Blueprint $table) {
            $table->bigIncrements('degreetype_id');
            $table->unsignedBigInteger('degreelevel_id')->index('degree_types_degreelevel_id_foreign');
            $table->string('type_name');
            $table->timestamps();
            $table->string('abbreviation', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_types');
    }
};
