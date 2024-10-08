<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitQuestion extends Model
{
    use HasFactory;
    use HasUuids;


    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
