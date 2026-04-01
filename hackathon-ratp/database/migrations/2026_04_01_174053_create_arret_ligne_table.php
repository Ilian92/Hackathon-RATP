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
        Schema::create('arret_ligne', function (Blueprint $table) {
            $table->foreignId('ligne_id')->constrained()->cascadeOnDelete();
            $table->foreignId('arret_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ordre');
            $table->primary(['ligne_id', 'arret_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arret_ligne');
    }
};
