<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Helpers\TelenorSMSAPI;
use App\Helpers\Widgets\LocationsWidget;
use App\Http\Requests\Admin\StoreUpdateLeadCommentsRequest;
use App\Jobs\LeadUserUpdateEmailGender;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\LeadComments;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Patients;
use App\Models\Regions;
use App\Models\Settings;
use App\Models\SMSLogs;
use App\Models\SMSTemplates;
use App\Models\Services;
use App\Models\Towns;
use App\Models\MetaLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FileUploadLeadsRequest;
use Auth;
use File;
use App\Helpers\GeneralFunctions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Config;
use App\Helpers\NodesTree;
use Session;
use DB;
use Illuminate\Support\Facades\Input;
use Validator;
use App\Models\Appointments;
use Illuminate\Foundation\Bus\DispatchesJobs;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Log;

class LeadController extends Controller
{
        public function store(Request $request)
        {
            MetaLog::create([
                'endpoint' => 'api/lead/storemetaleads',
                'method' => $request->method(),
                'request_data' => json_encode($request->all()), 
                'ip_address' => $request->ip(),
                'called_at' => now(),
            ]);

            Log::info('Meta webhook called', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $request->headers->all(),
                'body' => $request->getContent()
            ]);

            if ($request->isMethod('GET')) {
                return $this->handleVerification($request);
            }
            if ($request->isMethod('POST')) {
                return $this->handleLeadData($request);
            }

            return response('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }

        private function handleVerification(Request $request)
        {
           $qs = $request->server('QUERY_STRING') ?? parse_url($request->fullUrl(), PHP_URL_QUERY) ?? '';
            parse_str($qs, $query);

            $hubMode = $query['hub.mode'] ?? $query['hub_mode'] ?? $request->get('hub.mode') ?? $request->get('hub_mode');
            $hubVerifyToken = $query['hub.verify_token'] ?? $query['hub_verify_token'] ?? $request->get('hub.verify_token') ?? $request->get('hub_verify_token');
            $hubChallenge = $query['hub.challenge'] ?? $query['hub_challenge'] ?? $request->get('hub.challenge') ?? $request->get('hub_challenge');

            Log::info('Webhook verification attempt', [
                'hub_mode' => $hubMode,
                'hub_verify_token' => $hubVerifyToken ? 'present' : 'missing',
                'hub_challenge' => $hubChallenge ? 'present' : 'missing'
            ]);

            $expectedVerifyToken = env('FB_VERIFY_TOKEN', 'my_secret_token_123');
            if ($hubMode === 'subscribe') {
                if ($hubVerifyToken !== $expectedVerifyToken) {
                    Log::warning('FB webhook verification failed', [
                        'provided' => $hubVerifyToken,
                        'expected' => $expectedVerifyToken ? 'set' : 'not set'
                    ]);
                    return response('Forbidden', Response::HTTP_FORBIDDEN);
                }

                Log::info('Webhook verification successful');
                return response($hubChallenge, Response::HTTP_OK)->header('Content-Type', 'text/plain');
            }

            return response('Invalid hub mode', Response::HTTP_BAD_REQUEST);
        }

        private function handleLeadData(Request $request)
        {
            $payload = json_decode($request->getContent(), true) ?? $request->all();
            Log::info('FB webhook payload', ['payload' => $payload]);

            $query = $request->query();

            $leadgenId = $payload['entry'][0]['changes'][0]['value']['leadgen_id']
                ?? $payload['entry'][0]['changes'][0]['value']['leadgenId']
                ?? null;

            if (!$leadgenId) {
                Log::warning('No leadgen_id found in FB payload', ['payload' => $payload]);
                return response('EVENT_RECEIVED 1', \Symfony\Component\HttpFoundation\Response::HTTP_OK);
            }

            $pageAccessToken = env('FB_Access_Token');

            $fields = [];
            $leadData = []; 
            if ($pageAccessToken) {
                try {
                    $client = new \GuzzleHttp\Client([
                        'base_uri' => 'https://graph.facebook.com/v19.0/',
                        'timeout'  => 6.0,
                    ]);

                    $resp = $client->get($leadgenId, [
                        'query' => [
                            'access_token' => $pageAccessToken,
                            'fields'       => 'id,created_time,form_id,field_data,custom_disclaimer_responses,ad_id,adset_id,platform,adset_name,ad_name,campaign_name',
                        ],
                    ]);

                    if ($resp->getStatusCode() >= 200 && $resp->getStatusCode() < 300) {
                        $leadData = json_decode($resp->getBody()->getContents(), true) ?? [];
                        Log::info('FB lead details', ['leadData' => $leadData]);
                        foreach ($leadData['field_data'] ?? [] as $f) {
                            $name  = $f['name'] ?? null;
                            $value = $f['values'][0] ?? ($f['value'] ?? null);
                            if ($name !== null) {
                                $fields[$name] = $value;
                            }
                        }

                    } else {
                        Log::error('Failed to fetch lead details', [
                            'lead_id' => $leadgenId,
                            'status'  => $resp->getStatusCode(),
                            'body'    => (string) $resp->getBody(),
                        ]);
                        return response('EVENT_RECEIVED 2', \Symfony\Component\HttpFoundation\Response::HTTP_OK);
                    }
                } catch (\Throwable $e) {
                    Log::error('Error fetching lead details', [
                        'leadgen_id' => $leadgenId,
                        'exception'  => $e->getMessage(),
                    ]);
                    return response('EVENT_RECEIVED 3 - ' . $e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_OK);
                }
            } else {
                Log::warning('No FB_Access_Token provided, processing with query parameters', ['payload' => $payload]);
            }
            $location=NULL;
            if(!empty($leadData['adset_name']))
            {
             $location =  \App\Models\Locations::where('name', 'LIKE', "%{$leadData['adset_name']}%")->first();
            }
            $center_location = $query['center_location'] ?? ($fields['center_location'] ?? null);

            if (empty($location) && !empty($center_location)) {
                 $center_location= strtolower(trim($center_location));
                    $location = Locations::whereRaw("
                        LOWER(REPLACE(name, ' ', '_')) LIKE ?
                    ", ["%{$center_location}%"])->first();
            }
            $genderValue = null;
            if (isset($query['gender'])) {
                $genderValue = $query['gender'];
            } elseif (isset($fields['gender'])) {
                if ($fields['gender'] === 'male'|| $fields['gender'] === 'Male' || $fields['gender'] === 'MALE') {
                    $genderValue = 1;
                } elseif ($fields['gender'] === 'female' || $fields['gender'] === 'Female' || $fields['gender'] === 'Fe-Male') {
                    $genderValue = 2;
                } else {
                    $genderValue = null;
                }
            }
            $id = 1306609;
            if(!empty($leadData['platform'])){
            $id = $leadData['platform'] == 'ig' ? 1307771 : 1306609;
            }
            
            $data = [
                'phone'             => $query['phone'] 
                                        ?? ($fields['phone_number'] ?? ($fields['phone'] ?? null)),
                'name'              => $query['first_name'] 
                                        ?? ($fields['full_name'] ?? ($fields['name'] ?? 'Unknown')),
                'cnic'              => $query['cnic'] 
                                        ?? ($fields['cnic'] ?? null),
                'email'             => $query['email'] 
                                        ?? ($fields['email'] ?? null),
                'dob'               => $query['birthday'] 
                                        ?? ($fields['birthday'] ?? null),
                'address'           => $query['address'] 
                                        ?? ($fields['address'] ?? null),
                'gender'            => $genderValue,
                'account_id'        => 1,
                'created_by'        => $id,
                'updated_by'        => $id,
                'converted_by'      => $id,
                'lead_source_id'    => 30,
                'meta_service_name' => $leadData['campaign_name'] ?? null,
                'city_id'           => $location ? $location->city_id : null,
                'region_id'         => $location ? $location->region_id : null,
                'location_id'       => $location ? $location->id : null,
                'town_id'           => $location ? $location->id : null,
                'meta_center_name'  => $leadData['adset_name'] ?? null,
                'meta_center_location'  => $query['center_location'] ?? ($fields['center_location'] ?? null),
            ];
             if (!empty($leadData['campaign_name'])) {
                $service = Services::where('name', $leadData['campaign_name'])->first();
                if ($service) {
                    $data['service_id'] = $service->id;
                }
            }


            $existingPatient = null;

            if (!empty($data['phone'])) {
                $data['phone'] = \App\Helpers\GeneralFunctions::cleanNumber($data['phone']);
                $data['user_type_id'] = \Illuminate\Support\Facades\Config::get('constants.patient_id');
                $existingPatient = \App\Models\Patients::where('phone', $data['phone'])->first();
            }

            if ($existingPatient) {
                $patient = \App\Models\Patients::updateRecord($existingPatient->id, $data);
            } else {
                $data['is_meta'] = 1;
                $data['lead_status_id'] = 1;

                $patient = \App\Models\Patients::createRecord($data);
            }

            if (!$patient || !is_object($patient) || !isset($patient->id)) {
                Log::error('Failed to create or update patient', ['data' => $data]);
                return response('EVENT_RECEIVED 5', \Symfony\Component\HttpFoundation\Response::HTTP_OK);
            }

            $data['patient_id'] = $patient->id;
            $lead = null;

            // if (!$existingPatient) {
                $data['source'] = 'Meta';
                $lead = \App\Models\Leads::createRecord($data, $patient, $status = "Lead");
            // } else {
                // $lead = \App\Models\Leads::where('patient_id', $patient->id)->first();
            // }

            if (!$lead || !is_object($lead) || !isset($lead->patient_id)) {
                Log::error('Failed to create or update lead', [
                    'data'       => $data,
                    'patient_id' => $patient->id,
                ]);
                return response('EVENT_RECEIVED 6', \Symfony\Component\HttpFoundation\Response::HTTP_OK);
            }

            return response('EVENT_RECEIVED', \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        }

}