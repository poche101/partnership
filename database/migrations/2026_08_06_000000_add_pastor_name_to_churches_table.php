<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            if (! Schema::hasColumn('churches', 'pastor_name')) {
                $table->string('pastor_name')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            if (Schema::hasColumn('churches', 'pastor_name')) {
                $table->dropColumn('pastor_name');
            }
        });
    }
};