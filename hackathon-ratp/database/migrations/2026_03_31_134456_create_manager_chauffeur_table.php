<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_chauffeur', function (Blueprint $table) {
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chauffeur_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['manager_id', 'chauffeur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_chauffeur');
    }
};
