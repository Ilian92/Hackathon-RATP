<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centre_bus_user', function (Blueprint $table) {
            $table->foreignId('centre_bus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['centre_bus_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centre_bus_user');
    }
};
