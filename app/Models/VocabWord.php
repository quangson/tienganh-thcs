<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VocabWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vocab_set_id',
        'word',
        'ipa',
        'meaning',
        'example',
        'sort_order',
    ];

    public function vocabSet()
    {
        return $this->belongsTo(VocabSet::class);
    }
}
