<?php

namespace App\Models;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids as ConcernsHasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['visit_start','visit_end'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_sets');
    }

    public function questionset()
    {
        return $this->hasMany(QuestionSet::class);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }


}
