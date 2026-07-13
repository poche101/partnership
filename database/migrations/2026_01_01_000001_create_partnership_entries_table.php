<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();

            $table->decimal('rhapsody', 14, 2)->default(0);
            $table->decimal('healing_school', 14, 2)->default(0);
            $table->decimal('loveworld_programs', 14, 2)->default(0);
            $table->decimal('loveworld_networks', 14, 2)->default(0);
            $table->decimal('inner_city', 14, 2)->default(0);
            $table->decimal('ror_bible', 14, 2)->default(0);
            $table->decimal('blw_campus', 14, 2)->default(0);
            $table->decimal('new_media', 14, 2)->default(0);
            $table->decimal('ltm', 14, 2)->default(0);
            $table->decimal('loveworld_radio', 14, 2)->default(0);
            $table->decimal('lmam', 14, 2)->default(0);
            $table->decimal('crusade_grounds', 14, 2)->default(0);
            $table->decimal('lca_rebuild', 14, 2)->default(0);
            // Kept in sync by the PartnershipEntry model on save (see App\Models\PartnershipEntry).
            $table->decimal('total_espees', 14, 2)->default(0);

            $table->text('note')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['church_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_entries');
    }
};
