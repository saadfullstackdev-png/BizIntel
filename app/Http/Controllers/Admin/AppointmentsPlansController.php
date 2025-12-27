<?php

namespace App\Http\Controllers\Admin;

use App\Models\Appointments;
use App\Models\LeadSources;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use DB;
use Auth;
use Validator;
use App\Models\Packages;
use App\Models\PackageBundles;
use App\Models\PackageAdvances;
use App\Models\Discounts;
use App\Models\Services;
use App\User;
use Config;
use Carbon\Carbon;
use App\Models\PaymentModes;
use App\Models\PackageService;
use App\Helpers\Widgets\LocationsWidget;
use App\Models\Locations;
use App\Models\UserHasLocations;
use App\Models\Settings;
use App\Helpers\ACL;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;


class AppointmentsPlansController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if (!Gate::allows('patients_plan_create')) {
            return abort(401);
        }

        $appointmentinformation = Appointments::find($id);

        $locations = Locations::getActiveSorted(ACL::getUserCentres(),'full_address');
        $locations->prepend('Select Centers', '');

        $patients = User::find($appointmentinformation->patient_id);

        $random_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));

        $unique_id = md5(time() . rand(0001, 9999) . rand(78599, 99999));

        $paymentmodes = PaymentModes::where('type', '=', 'application')->pluck('name','id');
        $paymentmodes->prepend('Select Payment Mode','');

        $customdiscountrange = Settings::where('slug', '=', 'sys-discounts')->first();
        $range = explode(':', $customdiscountrange->data);

        $lead_sources = LeadSources::getActiveSorted();
        $lead_sources->prepend('Select a Lead Source', '');

        return view('admin.appointments.plans.create', compact('patients', 'locations', 'random_id', 'paymentmodes', 'range','appointmentinformation', 'unique_id', 'lead_sources'));
    }
}
