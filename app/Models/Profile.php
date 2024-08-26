<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    public function retrieve($profile_key=null)
    {
        return $this->where('auth_key', $profile_key)->first();
    }
}
