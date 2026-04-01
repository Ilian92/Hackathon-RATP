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
        Schema::create('qrcode_scan_limits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->unsignedInteger('count')->default(1);
            $table->timestamp('window_start');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qrcode_scan_limits');
    }
};
