<?php

namespace App\Http\Controllers\Admin\Reports;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\AppointmentExport;
use Barryvdh\DomPDF\Facade as PDF;
use Excel;
use App\Helpers\NodesTree;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use App\Models\Doctors;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\Services;
use App\Reports\Appointments;
use App\User;
use App\Models\Cities;
use App\Helpers\ACL;
use Auth;

class StaffAppointmentReportController extends Controller
{
    public function index() {
        return abort(401);
    }
    /*
      * Display filters for staff appointment report
      *
      * */
    public function staffreport(){

        $patients = Patients::getAll(Auth::User()->account_id);

        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities());
        $cities->prepend('All','');

        $doctors = Doctors::getActiveOnly(ACL::getUserCentres());
        $doctors->prepend('All','');

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All','');

        $parentGroups = new NodesTree();
        $parentGroups->current_id = -1;
        $parentGroups->build(0, Auth::User()->account_id);
        $parentGroups->toList($parentGroups, -1);
        $services = $parentGroups->nodeList;

        $appointment_statuses = AppointmentStatuses::getAllParentRecords(Auth::User()->account_id);
        if ($appointment_statuses) {
            $appointment_statuses = $appointment_statuses->pluck('name', 'id');
        }
        $appointment_statuses->prepend('All', '');

        $appointment_types = AppointmentTypes::getAllRecords(Auth::User()->account_id)->pluck('name', 'id');
        $appointment_types->prepend('All', '');

        $users = User::getAllActiveRecords(Auth::User()->account_id)->pluck('name', 'id');
        $users->prepend('All', '');

        return view('admin.reports.staffappointments.index', compact( 'patients', 'cities', 'doctors', 'locations', 'services', 'appointment_statuses', 'appointment_types', 'users'));
    }
}
