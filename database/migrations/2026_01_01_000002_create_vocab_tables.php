<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocab_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('level')->default('Khối 6 (Global Success)');
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('vocab_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vocab_set_id')->constrained('vocab_sets')->cascadeOnDelete();
            $table->string('word');
            $table->string('ipa')->nullable();
            $table->string('meaning')->nullable();
            $table->text('example')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocab_words');
        Schema::dropIfExists('vocab_sets');
    }
};
