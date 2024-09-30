<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSectionScore extends Model
{
    use HasFactory;
    protected $fillable = [
        'attempt_id',
        'section_id',
        'total_questions',
        'correct_answers',
        'score',
        'percentage'
    ];

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
