<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('content_type', ['vocab', 'passage']);
            $table->foreignId('vocab_set_id')->nullable()->constrained('vocab_sets')->cascadeOnDelete();
            $table->foreignId('passage_id')->nullable()->constrained('passages')->cascadeOnDelete();
            $table->enum('target_type', ['class', 'students'])->default('class');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('pass_score')->default(75);
            $table->text('teacher_note')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('assignment_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['assignment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_student');
        Schema::dropIfExists('assignments');
    }
};
