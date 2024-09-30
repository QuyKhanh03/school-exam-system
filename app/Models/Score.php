<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    protected $fillable = [
        'attempt_id',
        'subject_id',
        'total_questions',
        'correct_answers',
        'score',
        'percentage'
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
