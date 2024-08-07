<?php

namespace App\Models;

//use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = ['name'];

    public function uniqueIds(): array
{
    return ['id', 'uuid'];
}

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
