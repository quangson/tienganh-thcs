<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content_type',
        'vocab_set_id',
        'passage_id',
        'target_type',
        'school_class_id',
        'due_date',
        'pass_score',
        'teacher_note',
        'teacher_id',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    public function vocabSet()
    {
        return $this->belongsTo(VocabSet::class);
    }

    public function passage()
    {
        return $this->belongsTo(Passage::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function targetStudents()
    {
        return $this->belongsToMany(User::class, 'assignment_student', 'assignment_id', 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
