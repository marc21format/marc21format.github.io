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
        Schema::create('attendance', function (Blueprint $table) {
            $table->bigIncrements('attendance_id');
            $table->unsignedBigInteger('user_id')->unique('user_id_unique');
            $table->timestamps();
            $table->softDeletes();
            $table->enum('session', ['am', 'pm'])->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable()->index('attendance_user_id_foreign_key_recorded_by_idx');
            $table->date('date');
            $table->unsignedBigInteger('updated_by')->nullable()->index('user_id_foreign_updated_by_idx');
            $table->time('attendance_time')->nullable();
            $table->unsignedBigInteger('letter_id')->nullable()->index('attendance_letter_id_foreign_idx');

            $table->unique(['user_id', 'date', 'session'], 'ux_user_date_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
