<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateType extends Model
{
    use SoftDeletes;

    protected $fillable = ['date_type', 'slug', 'account_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $table = 'date_types';
}
