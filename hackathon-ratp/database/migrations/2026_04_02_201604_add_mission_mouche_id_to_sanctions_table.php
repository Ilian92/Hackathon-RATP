<?php

use App\Models\MissionMouche;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->foreignId('mission_mouche_id')->nullable()->after('complaint_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sanctions', function (Blueprint $table) {
            $table->dropForeignIdFor(MissionMouche::class);
            $table->dropColumn('mission_mouche_id');
        });
    }
};
