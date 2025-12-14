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
        Schema::create('addresses', function (Blueprint $table) {
            $table->bigIncrements('address_id');
            $table->unsignedBigInteger('barangay_id')->nullable()->index('addresses_barangay_id_foreign');
            $table->unsignedBigInteger('city_id')->nullable()->index('addresses_city_id_foreign');
            $table->timestamps();
            $table->softDeletes();
            $table->string('house_number', 45)->nullable();
            $table->string('block_number', 45)->nullable();
            $table->string('street', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
