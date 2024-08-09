<?php

namespace App\Models;

//use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agency extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['name','uuid','profile_id'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
