<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountApproval extends Model
{
    protected $fillable = ['discount_id', 'user_id', 'created_at', 'updated_at'];

    protected $table = 'discount_approvals';

    public function discount()
    {
        return $this->belongsTo('App\Models\Discounts', 'discount_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
