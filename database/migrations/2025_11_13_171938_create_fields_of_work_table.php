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
        Schema::create('fields_of_work', function (Blueprint $table) {
            $table->bigIncrements('fieldofwork_id');
            $table->string('name');
            $table->timestamps();
            $table->unsignedBigInteger('prefix_id')->nullable()->index('prefix_title_idx');
            $table->unsignedBigInteger('suffix_id')->nullable()->index('suffix_title_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields_of_work');
    }
};
