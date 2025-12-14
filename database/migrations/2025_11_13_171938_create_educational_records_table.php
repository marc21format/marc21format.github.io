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
        Schema::create('educational_records', function (Blueprint $table) {
            $table->bigInteger('record_id', true);
            $table->unsignedBigInteger('degreeprogram_id')->index('degreeprogram_id_foreign_idx');
            $table->unsignedBigInteger('user_id')->index('users_id_foreign_idx');
            $table->year('year_start')->nullable();
            $table->year('year_end')->nullable();
            $table->unsignedBigInteger('university_id')->index('university_id_foreign_idx');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->tinyInteger('DOST_Scholarship')->nullable()->default(0);
            $table->enum('latin_honor', ['Cum Laude', 'Magna Cum Laude', 'Summa Cum Laude'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_records');
    }
};
