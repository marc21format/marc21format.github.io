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
        Schema::create('highschools', function (Blueprint $table) {
            $table->bigIncrements('highschool_id');
            $table->string('highschool_name');
            $table->timestamps();
            $table->softDeletes();
            $table->string('abbreviation', 45)->nullable();
            $table->string('type', 45);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('highschools');
    }
};
