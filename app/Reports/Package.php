<?php

namespace App\Reports;

use App\Helpers\ACL;
use App\Models\Appointments;
use App\Models\Bundles;
use App\Models\Locations;
use Config;
use DB;
use Auth;

class Package
{
    /**
     * Centre performance stats by revenue
     * @param (mixed) $request
     * @return (mixed)
     */
    public static function PackagesalecountReport($data, $filters = array())
    {
        $where = array();
        if (isset($data['date_range']) && $data['date_range']) {
            $date_range = explode(' - ', $data['date_range']);
            $start_date = date('Y-m-d', strtotime($date_range[0]));
            $end_date = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $start_date = null;
            $end_date = null;
        }
        if (isset($data['location_id']) && $data['location_id']) {
            $where[] = array(
                'package_bundles.location_id',
                '=',
                $data['location_id']
            );
        }
        if (isset($data['package_id']) && $data['package_id']) {
            $where[] = array(
                'package_bundles.bundle_id',
                '=',
                $data['package_id']
            );
        }
        $where[] = array(
            'bundles.type',
            '=',
            'multiple'
        );

        $records = Bundles::join('package_bundles', 'package_bundles.bundle_id', '=', 'bundles.id')
            ->whereDate('package_bundles.created_at', '>=', $start_date)
            ->whereDate('package_bundles.created_at', '<=', $end_date)
            ->whereIn('location_id', ACL::getUserCentres())
            ->where($where)
            ->select('package_bundles.*')
            ->get();

        $data = array();
        $ids = array();

        foreach ($records as $record) {
            $key = $record->location_id . '-' . $record->bundle_id;
            if (!in_array($key, $ids)) {
                $ids[] = $key;
                $data[$key] = array(
                    'location' => Locations::find($record->location_id)->name,
                    'package' => Bundles::find($record->bundle_id)->name,
                    'count' => 0,
                );
            }
            $data[$key]['count']++;
        }

        return array_values($data);
    }
}
