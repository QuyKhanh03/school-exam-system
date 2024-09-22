<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'attempt_id',
        'answer_text'
    ];
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function attempt()
    {
        return $this->belongsTo(Attempt::class);
    }

    public function answerOptions()
    {
        return $this->hasMany(AnswerOption::class);
    }

}
