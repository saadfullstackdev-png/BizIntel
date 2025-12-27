<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationLog extends Model
{
    use SoftDeletes;

    protected $fillable = ['log_type', 'to', 'text', 'title', 'type', 'value', 'icon', 'status', 'error_msg', 'lead_id', 'appointment_id', 'invoice_id', 'package_id', 'promotion_id', 'is_refund', 'created_by', 'patient_id', 'is_read', 'created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['log_type', 'to', 'text', 'title', 'type', 'value', 'icon', 'status', 'error_msg', 'lead_id', 'appointment_id', 'invoice_id', 'package_id', 'promotion_id', 'is_refund', 'created_by', 'patient_id', 'is_read'];

    protected $table = 'notification_logs';

    protected static $_table = 'notification_logs';

    static public function getNotifications($user_id)
    {
        return self::where('patient_id', '=', $user_id)->OrderBy('id', 'desc')->select('id', 'text', 'title', 'icon', 'is_read')->get()->toArray();
    }
}
