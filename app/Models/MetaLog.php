<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaLog extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'request_data',
        'ip_address',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];
}
