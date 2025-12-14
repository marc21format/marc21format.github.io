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
        Schema::create('committee_members', function (Blueprint $table) {
            $table->bigIncrements('member_id');
            $table->unsignedBigInteger('user_id')->index('committee_members_user_id_foreign');
            $table->unsignedBigInteger('committee_id')->index('committee_members_committee_id_foreign');
            $table->unsignedBigInteger('position_id')->index('committee_members_position_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
