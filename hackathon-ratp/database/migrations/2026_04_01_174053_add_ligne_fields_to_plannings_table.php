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
        Schema::table('plannings', function (Blueprint $table) {
            $table->foreignId('ligne_id')->nullable()->after('bus_id')->constrained()->nullOnDelete();
            $table->foreignId('arret_debut_id')->nullable()->after('ligne_id')->constrained('arrets')->nullOnDelete();
            $table->time('heure_debut')->nullable()->after('arret_debut_id');
            $table->foreignId('arret_fin_id')->nullable()->after('heure_debut')->constrained('arrets')->nullOnDelete();
            $table->time('heure_fin')->nullable()->after('arret_fin_id');
        });
    }

    public function down(): void
    {
        Schema::table('plannings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arret_fin_id');
            $table->dropColumn('heure_fin');
            $table->dropConstrainedForeignId('arret_debut_id');
            $table->dropColumn('heure_debut');
            $table->dropConstrainedForeignId('ligne_id');
        });
    }
};
