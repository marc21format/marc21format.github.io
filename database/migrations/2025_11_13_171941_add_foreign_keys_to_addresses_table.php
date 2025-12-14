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
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign(['barangay_id'])->references(['barangay_id'])->on('barangays')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['city_id'])->references(['city_id'])->on('cities')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_barangay_id_foreign');
            $table->dropForeign('addresses_city_id_foreign');
        });
    }
};
