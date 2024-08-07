<?php

namespace App\Models;

//use App\Traits\HasUuids;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['question', 'type', 'choices', 'agency_id'];

    public function uniqueIds(): array
    {
        return ['id', 'uuid'];
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected function casts(): array
    {
        return [
            'choices' => 'json',
        ];
    }

    public function agency():BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
