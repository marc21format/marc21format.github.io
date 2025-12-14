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
        Schema::create('rooms', function (Blueprint $table) {
            $table->bigIncrements('room_id');
            $table->string('group');
            $table->unsignedBigInteger('adviser_id')->nullable()->index('rooms_adviser_id_foreign');
            $table->unsignedBigInteger('president_id')->nullable()->index('rooms_president_id_foreign');
            $table->unsignedBigInteger('secretary_id')->nullable()->index('rooms_secretary_id_foreign');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
