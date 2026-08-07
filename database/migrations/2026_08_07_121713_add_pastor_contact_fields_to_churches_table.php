<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            // pastor_name already exists (seeded via ChurchDataSeeder).
            $table->string('pastor_email')->nullable()->after('pastor_name');
            $table->string('pastor_phone')->nullable()->after('pastor_email');
            $table->string('pastor_kingschat')->nullable()->after('pastor_phone');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['pastor_email', 'pastor_phone', 'pastor_kingschat']);
        });
    }
};
