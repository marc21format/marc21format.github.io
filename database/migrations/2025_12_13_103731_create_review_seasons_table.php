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
        Schema::create('review_seasons', function (Blueprint $table) {
            $table->id();
            $table->integer('start_month');
            $table->integer('start_year');
            $table->integer('end_month');
            $table->integer('end_year');
            $table->boolean('is_active')->default(true);
            $table->foreignId('set_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_seasons');
    }
};
