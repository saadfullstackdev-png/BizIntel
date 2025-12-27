<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionCharge extends Model
{
    // Enable timestamps
    public $timestamps = true;

    // Define the fillable attributes
    protected $fillable = [
        'amount',
        'banner',
        'account_id'
    ];

    // Define the table name
    protected $table = 'subscription_charges';

    /**
     * Define the relationship between SubscriptionCharge and Category.
     * A subscription charge can have many categories (many-to-many).
     */
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_subscription_charge', // Pivot table name
            'subscription_charge_id',      // Foreign key on pivot table for SubscriptionCharge
            'category_id'                  // Foreign key on pivot table for Category
        )->withPivot('offered_discount')  // Include the additional column
          ->withTimestamps();             // Include pivot table timestamps
    }
}
