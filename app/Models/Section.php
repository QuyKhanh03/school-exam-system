<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['exam_id', 'name', 'is_mandatory','timing'];
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'section_subjects', 'section_id', 'subject_id');
    }
//    public function questions()
//    {
//        return $this->hasManyThrough(
//            Question::class,
//            SectionQuestion::class,
//            'section_id',  // Khóa ngoại trong bảng section_questions
//            'id',          // Khóa chính trong bảng questions
//            'id',          // Khóa chính trong bảng sections
//            'question_id'  // Khóa ngoại trong bảng section_questions
//        );
//    }
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'section_questions', 'section_id', 'question_id');
    }
}
