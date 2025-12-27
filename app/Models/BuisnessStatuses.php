<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuisnessStatuses extends Model
{
    use SoftDeletes;

    protected $table = 'buisness_statuses';

    protected $fillable = ['name', 'active', 'account_id'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->account_id = auth()->user()->account_id ?? 1;
        });
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }
    static public function getAllParentRecords($account_id)
    {
        return self::where(['account_id' => $account_id])->get();
    }
    static public function getAllRecordsDictionary($account_id)
    {
        return self::where(['account_id' => $account_id])->get()->getDictionary();
    }
}