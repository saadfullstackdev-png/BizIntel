<?php

namespace App\Models;

use App\Helpers\Filters;
use App\Helpers\GeneralFunctions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use PHPUnit\Util\Filter;
use Config;
use Illuminate\Database\Eloquent\Model;


class CardSubscription extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'card_number',
        'patient_id',
        'account_id',
        'subscription_date',
        'expiry_date',
        'location',
        'is_app',
        'is_active',
    ];

    protected $table = 'card_subscriptions';

    public function patient()
    {
        return $this->belongsTo(Patients::class, 'patient_id', 'id');
    }


    
}
