<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapport_mouches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_mouche_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mouche_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ligne_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_observation');
            // Critères d'évaluation — note de 1 (très mauvais) à 5 (excellent)
            $table->tinyInteger('ponctualite');        // Respect des horaires
            $table->tinyInteger('conduite');           // Qualité de la conduite
            $table->tinyInteger('politesse');          // Politesse envers les passagers
            $table->tinyInteger('tenue');              // Tenue et présentation
            $table->tinyInteger('securite');           // Sécurité (portes, annonces, arrêts)
            $table->tinyInteger('gestion_conflit')->nullable(); // Gestion d'incident si applicable
            $table->text('observation')->nullable();   // Commentaire libre
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapport_mouches');
    }
};
