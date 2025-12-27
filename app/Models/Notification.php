<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModal
{
     use SoftDeletes;

    protected $fillable = ['user_id', 'icon', 'body','action_text', 'action_url', 'read', 'account_id', 'created_by','updated_by', 'created_at','updated_at', 'deleted_at'];

    protected $table = 'notifications';


    public function user()
    {
        return $this->belongsTo('App\User', 'id');
    }
}
