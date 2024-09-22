<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'answer_id',
        'option_id'
    ];
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
