<?php

use App\Enums\ComplaintStep;
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
            $table->enum('step', array_column(ComplaintStep::cases(), 'value'))
                ->default(ComplaintStep::ComReview->value)
                ->after('status');
            $table->foreignId('com_user_id')->nullable()->after('step')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('rh_user_id')->nullable()->after('com_user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('com_user_id');
            $table->dropConstrainedForeignId('rh_user_id');
            $table->dropColumn('step');
        });
    }
};
