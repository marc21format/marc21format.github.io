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
        Schema::create('highschool_subject_records', function (Blueprint $table) {
            $table->bigIncrements('record_id');
            $table->unsignedBigInteger('user_id')->index('highschool_subject_records_user_id_foreign');
            $table->unsignedBigInteger('highschoolsubject_id')->index('highschool_subject_records_highschoolsubject_id_foreign');
            $table->string('grade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('highschool_subject_records');
    }
};
