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
        Schema::table('fields_of_work', function (Blueprint $table) {
            $table->foreign(['prefix_id'], 'prefix_title')->references(['prefix_id'])->on('prefix_titles')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['suffix_id'], 'suffix_title')->references(['suffix_id'])->on('suffix_titles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields_of_work', function (Blueprint $table) {
            $table->dropForeign('prefix_title');
            $table->dropForeign('suffix_title');
        });
    }
};
