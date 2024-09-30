<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['exam_id', 'name', 'is_mandatory','timing'];
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'section_subjects', 'section_id', 'subject_id');
    }

    public function sectionScores()
    {
        return $this->hasMany(ExamSectionScore::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'section_questions', 'section_id', 'question_id');
    }
}
