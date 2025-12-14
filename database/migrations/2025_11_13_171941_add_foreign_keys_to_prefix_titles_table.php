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
        Schema::table('prefix_titles', function (Blueprint $table) {
            $table->foreign(['fieldofwork_id'], 'fieldofwork_id_foreign')->references(['fieldofwork_id'])->on('fields_of_work')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prefix_titles', function (Blueprint $table) {
            $table->dropForeign('fieldofwork_id_foreign');
        });
    }
};
