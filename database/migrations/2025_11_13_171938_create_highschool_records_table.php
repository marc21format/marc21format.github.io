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
        Schema::create('highschool_records', function (Blueprint $table) {
            $table->bigIncrements('record_id');
            $table->unsignedBigInteger('user_id')->index('highschool_records_user_id_foreign');
            $table->unsignedBigInteger('highschool_id')->nullable()->index('highschool_records_highschool_id_foreign');
            $table->enum('level', ['junior', 'senior'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->year('year_end')->nullable();
            $table->year('year_start')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('highschool_records');
    }
};
