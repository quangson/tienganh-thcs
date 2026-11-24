<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade',
        'description',
        'teacher_id',
    ];

    public function students()
    {
        return $this->hasMany(User::class, 'school_class_id')->where('role', 'student');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'school_class_id');
    }
}
