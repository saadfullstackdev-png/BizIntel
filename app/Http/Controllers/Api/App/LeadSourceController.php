<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\ACL;
use App\Helpers\GeneralFunctions;
use App\Helpers\JazzSMSAPI;
use App\Helpers\NodesTree;
use App\Helpers\TelenorSMSAPI;
use App\Helpers\Widgets\AppointmentCheckesWidget;
use App\Helpers\Widgets\LocationsWidget;
use App\Http\Resources\CityResource;
use App\Http\Resources\DoctorRotasResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\ResourcesResource;
use App\Http\Traits\Generic;
use App\Jobs\IndexSingleAppointmentJob;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\Invoices;
use App\Models\InvoiceStatuses;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Http\Controllers\Controller;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\ResourceHasRota;
use App\Models\ResourceHasRotaDays;
use App\Models\Resources;
use App\Helpers\GroupsTree;
use App\Models\Services;
use App\Models\Settings;
use App\Models\SMSLogs;
use App\Models\SMSTemplates;
use App\Models\Towns;
use App\Models\UserOperatorSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class LeadSourceController extends Controller
{
    public function index()
    {
        // get list of lead sources like whatsapp facebook etc
        $leads = LeadSources::where('active', 1)->get();
        return response()->json([
            'status' => true,
            'message' => 'Lead Source list',
            'data' => $leads,
            'status_code' => 200,
        ]);
    }
}
