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
        Schema::create('professional_credentials', function (Blueprint $table) {
            $table->bigIncrements('credential_id');
            $table->unsignedBigInteger('user_id')->index('professional_credentials_user_id_foreign');
            $table->unsignedBigInteger('fieldofwork_id')->nullable()->index('professional_credentials_fieldofwork_id_foreign');
            $table->unsignedBigInteger('prefix_id')->nullable()->index('professional_credentials_prefix_id_foreign');
            $table->unsignedBigInteger('suffix_id')->nullable()->index('professional_credentials_suffix_id_foreign');
            $table->date('issued_on')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_credentials');
    }
};
