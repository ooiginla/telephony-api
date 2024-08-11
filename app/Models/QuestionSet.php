<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'question_type', 'selected_answer', 'question_no', 'uuid','visit_id'];
    
    public function questions():HasMany
    {
        return $this->hasMany(Question::class);
    }
}
