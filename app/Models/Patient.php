<?php

namespace App\Models;

//use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    use HasFactory;

    use HasUuids;

    protected $fillable = ['phone_number', 'agency_id','uuid','profile_id'];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function agency():BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
