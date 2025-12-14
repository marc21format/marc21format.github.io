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
        Schema::create('committee_positions', function (Blueprint $table) {
            $table->bigIncrements('committeeposition_id');
            $table->unsignedBigInteger('position_id')->nullable()->index('positions_idx');
            $table->unsignedBigInteger('committee_id')->nullable()->index('committees_idx');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_positions');
    }
};
