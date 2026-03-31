<?php

use App\Enums\UserStatus;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('matricule')->unique()->nullable()->after('id');
            $table->date('contract_start_date')->nullable()->after('role');
            $table->enum('status', array_column(UserStatus::cases(), 'value'))
                ->default(UserStatus::Actif->value)
                ->after('contract_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['matricule', 'contract_start_date', 'status']);
        });
    }
};
