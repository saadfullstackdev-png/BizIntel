<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentDisplayType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['name','created_at', 'updated_at', 'deleted_at'];

    protected $table = 'content_display_type';

    protected static $_table = 'content_display_type';
}
