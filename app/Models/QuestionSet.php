<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    use HasFactory;

    protected $fillable = [  
    'visit_id',
    'question_id',
    'question_type',
    'question_order',
    'selected_key',
    'selected_answer',
    'answered_date',
    'profile_id'
];
    
    public function questions():HasMany
    {
        return $this->hasMany(Question::class);
    }
}
