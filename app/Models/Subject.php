<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description'
    ];
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_subjects', 'subject_id', 'section_id');
    }
}
