<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CardSubscriptionDetail extends Model
{
    protected $table = 'card_subscription_details';

    protected $fillable = [
        'subscription_card_id',
        'amount',
        'account_id',
    ];

}
