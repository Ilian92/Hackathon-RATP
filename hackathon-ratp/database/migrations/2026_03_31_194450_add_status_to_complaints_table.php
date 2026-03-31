<?php

use App\Enums\ComplaintStatus;
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
        Schema::table('complaints', function (Blueprint $table) {
            $table->enum('status', array_column(ComplaintStatus::cases(), 'value'))
                ->default(ComplaintStatus::EnCours->value)
                ->after('incident_time');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
