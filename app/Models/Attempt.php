<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'exam_id',
        'current_section',
        'score',
        'total_score',
        'started_at',
        'finished_at'
    ];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sectionScores()
    {
        return $this->hasMany(ExamSectionScore::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}
