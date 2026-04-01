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
        Schema::create('qrcode_satisfaction_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('token', 64);
            $table->timestamps();

            $table->unique(['ip_address', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qrcode_satisfaction_submissions');
    }
};
