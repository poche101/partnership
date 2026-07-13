<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained('churches')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('delegate_category')->nullable();
            $table->string('kingschat_username')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('group_name')->nullable();
            $table->string('church_category')->nullable();
            $table->string('spouse_title')->nullable();
            $table->string('spouse_first_name')->nullable();
            $table->string('spouse_delegate_category')->nullable();
            $table->string('spouse_kingschat')->nullable();
            $table->string('spouse_phone')->nullable();
            $table->string('spouse_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
