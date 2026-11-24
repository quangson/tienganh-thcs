<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'level',
        'description',
        'teacher_id',
    ];

    public function words()
    {
        return $this->hasMany(VocabWord::class)->orderBy('sort_order')->orderBy('id');
    }
}
