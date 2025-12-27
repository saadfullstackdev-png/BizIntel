<?php
/**
 * Created by PhpStorm.
 * User: REDSignal
 * Date: 3/22/2018
 * Time: 3:49 PM
 */

namespace App\Helpers;
use App\User;
use Auth;
use Carbon\Carbon;
class dateType {
    /**
     * This function return date type in case of date range
     */
    static function dateTypeDecision($start_date, $end_date){

        $user = User::find(Auth::user()->id);

        foreach ($user->roles()->pluck('date_type_id') as $type) {
            if ($type) {
                $date_type = \App\Models\DateType::find($type);
                $types[] = $date_type->slug;
            }
        }
        $status = 0;
        if (in_array("this_month", $types) && $status == 0)
        {
            $date = 'this_month';
            $status = 1;
        }
        if (in_array("this_year", $types) && $status == 0)
        {
            $date = 'this_year';
            $status = 1;
        }
        if (in_array("open", $types) && $status == 0)
        {
            $date = 'open';
            $status = 1;
        }

        $now = Carbon::now();

        $finalstatus = true;

        if($date == 'this_month'){
            $start = $now->startOfMonth()->format('Y-m-d');
            $end = $now->endOfMonth()->format('Y-m-d');

            if($start_date >= $start && $end_date <= $end){
                $finalstatus = true;
            } else {
                $finalstatus = false;
            }
        }
        if($date == 'this_year'){
            $start = $now->startOfYear()->format('Y-m-d');
            $end = $now->endOfYear()->format('Y-m-d');

            if($start_date >= $start && $end_date <= $end){
                $finalstatus = true;
            } else {
                $finalstatus = false;
            }
        }
        if(!$finalstatus){
            return array(
                'status' => $finalstatus,
                'message' => "You only fetch data between $start and $end"
            );
        }
        return array(
            'status' => $finalstatus,
        );
    }
    /**
     * This function return date type in case of year and month
     */
    static function dateTypeDecision_type_2($data){

        $user = User::find(Auth::user()->id);

        foreach ($user->roles()->pluck('date_type_id') as $type) {
            if ($type) {
                $date_type = \App\Models\DateType::find($type);
                $types[] = $date_type->slug;
            }
        }
        $status = 0;
        if (in_array("this_month", $types) && $status == 0)
        {
            $date = 'this_month';
            $status = 1;
        }
        if (in_array("this_year", $types) && $status == 0)
        {
            $date = 'this_year';
            $status = 1;
        }
        if (in_array("open", $types) && $status == 0)
        {
            $date = 'open';
            $status = 1;
        }

        $now = Carbon::now();

        $finalstatus = true;

        if($date == 'this_month'){
            $month = $now->format('m');
            $year = $now->format('Y');

            if($month == $data['month'] && $year == $data['year']){
                $finalstatus = true;
            } else {
                $finalstatus = false;
            }

            $info = Carbon::createFromDate($year,$month)->format('M');

            $message = "You can only fetch data of month $info and year $year";
        }
        if($date == 'this_year'){
            $year = $now->format('Y');

            if($year == $data['year']){
                $finalstatus = true;
            } else {
                $finalstatus = false;
            }
            $message = "You can only fetch data of year $year";
        }
        if(!$finalstatus){
            return array(
                'status' => $finalstatus,
                'message' => $message
            );
        }
        return array(
            'status' => $finalstatus,
        );
    }
}