<?php

namespace App\Models;

//use App\Traits\HasUuids;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['question', 'type', 'choices', 'agency_id','hash'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // protected function casts(): array
    // {
    //     return [
    //         'choices' => 'json',
    //     ];
    // }

    public function agency():BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function visits():BelongsToMany
    {
        return $this->belongsToMany(Visit::class, 'question_sets');
    }

}
