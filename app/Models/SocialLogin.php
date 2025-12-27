<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLogin extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'social_account_id',
        'social_account_type',
        'social_account_email',
    ];
}
