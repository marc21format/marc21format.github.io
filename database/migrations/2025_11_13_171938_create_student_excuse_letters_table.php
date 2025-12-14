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
        Schema::create('student_excuse_letters', function (Blueprint $table) {
            $table->bigIncrements('letter_id');
            $table->unsignedBigInteger('user_id')->index('student_excuse_letters_user_id_foreign');
            $table->string('reason/note');
            $table->date('date_attendance');
            $table->string('status')->nullable();
            $table->timestamps();
            $table->string('letter_link', 250);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_excuse_letters');
    }
};
