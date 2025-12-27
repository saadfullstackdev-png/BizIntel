<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Temporary extends Model
{
    protected $table = 'temporary';
    protected $guarded = [];
    public $timestamps = false;
}
