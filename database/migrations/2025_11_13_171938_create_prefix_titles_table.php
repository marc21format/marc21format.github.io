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
        Schema::create('prefix_titles', function (Blueprint $table) {
            $table->bigIncrements('prefix_id');
            $table->string('title');
            $table->string('abbreviation', 10)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('fieldofwork_id')->nullable()->index('fieldofwork_id_foreign_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prefix_titles');
    }
};
