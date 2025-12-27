<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronTest extends Model
{
    protected $fillable = ['description','created_at', 'updated_at'];
    protected $table = 'crontests';
}
