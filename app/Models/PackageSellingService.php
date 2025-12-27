<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageSellingService extends Model
{
    use SoftDeletes;

    protected $fillable = ['package_selling_id', 'service_id ', 'autual_price', 'offered_price', 'is_exclusive', 'tax_exclusive_price', 'tax_percentage', 'tax_price', 'tax_including_price', 'created_at', 'updated_at','deleted_at'];

    protected static $_fillable = ['package_selling_id', 'service_id ', 'autual_price', 'offered_price', 'is_exclusive', 'tax_exclusive_price', 'tax_percentage', 'tax_price', 'tax_including_price'];

    protected $table = 'package_selling_services';

    protected static $_table = 'package_selling_services';

    /*
     * get the service
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Services', 'service_id')->withTrashed();
    }
}
