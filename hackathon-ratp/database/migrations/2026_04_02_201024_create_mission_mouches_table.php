<?php

use App\Enums\MissionMoucheStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_mouches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default(MissionMoucheStatus::EnCours->value);
            $table->string('decision')->nullable();
            $table->text('manager_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_mouches');
    }
};
