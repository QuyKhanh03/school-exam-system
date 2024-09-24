<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'subject_id',
        'parent_id',
        'name',
        'type',
        'is_group'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function parentQuestion()
    {
        return $this->belongsTo(Question::class, 'parent_id');
    }

    public function subQuestions()
    {
        return $this->hasMany(Question::class, 'parent_id');
    }
}
