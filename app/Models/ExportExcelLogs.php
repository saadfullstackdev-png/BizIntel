<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ExportExcelLogs extends Model
{
    protected $fillable = ['user_id', 'exported_model', 'excel_path'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
