<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentReschedule extends Model
{


    protected $fillable = ['appointment_id', 'user_id', 'scheduled_date', 'scheduled_time', 'created_at', 'updated_at'];

    protected $table = 'appointment_reschedules';
}
