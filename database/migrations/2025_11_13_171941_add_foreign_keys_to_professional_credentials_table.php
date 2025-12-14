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
        Schema::table('professional_credentials', function (Blueprint $table) {
            $table->foreign(['fieldofwork_id'])->references(['fieldofwork_id'])->on('fields_of_work')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['prefix_id'])->references(['prefix_id'])->on('prefix_titles')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['suffix_id'])->references(['suffix_id'])->on('suffix_titles')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_credentials', function (Blueprint $table) {
            $table->dropForeign('professional_credentials_fieldofwork_id_foreign');
            $table->dropForeign('professional_credentials_prefix_id_foreign');
            $table->dropForeign('professional_credentials_suffix_id_foreign');
            $table->dropForeign('professional_credentials_user_id_foreign');
        });
    }
};
