<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giving_alert_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('arm_key')->unique();
            $table->decimal('threshold_espees', 14, 2);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('giving_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('partnership_entries')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->string('arm_key');
            $table->decimal('amount_espees', 14, 2);
            $table->decimal('threshold_espees', 14, 2);
            $table->boolean('acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['church_id', 'created_at']);
            $table->index(['acknowledged', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_alerts');
        Schema::dropIfExists('giving_alert_thresholds');
    }
};
