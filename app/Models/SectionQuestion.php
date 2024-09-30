<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionQuestion  extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'question_id', 'subject_id'];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
