<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Models\Accounts;
use App\Models\Cities;
use App\Models\Locations;
use App\Models\PabaoRecordPayments;
use App\Models\PabaoRecords;
use App\Models\Patients;
use App\Models\Services;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FileUploadPabaoRecordsRequest;
use Auth;
use File;
//Excel Library
use App\Helpers\GeneralFunctions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Config;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Session;
use DB;
use Validator;
use App;

class PabaoRecordsController extends Controller
{
    /**
     * Display a listing of PabaoRecord.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('pabao_records_manage')) {
            return abort(401);
        }

        $filters = Filters::all(Auth::User()->id, 'pabao_records');

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('All', '');

        return view('admin.pabao_records.index', compact('locations', 'filters'));
    }

    /**
     * Display a listing of PabaoRecord_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        $where = array();

        /*
         * Reset form filter is applied
         */
        $apply_filter = false;
        if ($request->get('action')) {
            $action = $request->get('action');
            if (isset($action[0]) && $action[0] == 'filter_cancel') {
                Filters::flush(Auth::User()->id, 'pabao_records');
            } else if ($action == 'filter') {
                $apply_filter = true;
            }
        }

        if ($request->get('order')) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            if ($orderBy == 'created_at') {
                $orderBy = 'pabao_records.created_at';
            }
            $order = $request->get('order')[0]['dir'];

            Filters::put(Auth::User()->id, 'pabao_records', 'order_by', $orderBy);
            Filters::put(Auth::User()->id, 'pabao_records', 'order', $order);
        } else {
            if (
                Filters::get(Auth::User()->id, 'pabao_records', 'order_by')
                && Filters::get(Auth::User()->id, 'pabao_records', 'order')
            ) {
                $orderBy = Filters::get(Auth::User()->id, 'pabao_records', 'order_by');
                $order = Filters::get(Auth::User()->id, 'pabao_records', 'order');

                if ($orderBy == 'created_at') {
                    $orderBy = 'pabao_records.created_at';
                }
            } else {
                $orderBy = 'created_at';
                $order = 'desc';
                if ($orderBy == 'created_at') {
                    $orderBy = 'pabao_records.created_at';
                }

                Filters::put(Auth::User()->id, 'pabao_records', 'order_by', $orderBy);
                Filters::put(Auth::User()->id, 'pabao_records', 'order', $order);
            }
        }

        if ($request->get('location_id') && $request->get('location_id') != '') {
            $where[] = array(
                'pabao_records.location_id',
                '=',
                $request->get('location_id')
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'location_id', $request->get('location_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'location_id');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'location_id')) {
                    $where[] = array(
                        'pabao_records.location_id',
                        '=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'location_id')
                    );
                }
            }
        }

        if ($request->get('client') && $request->get('client') != '') {
            $where[] = array(
                'pabao_records.client',
                'like',
                '%' . $request->get('client') . '%'
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'client', $request->get('client'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'client');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'client')) {
                    $where[] = array(
                        'pabao_records.client',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'pabao_records', 'client') . '%'
                    );
                }
            }
        }


        if ($request->get('phone') && $request->get('phone') != '') {
            $where[] = array(
                'pabao_records.phone',
                'like',
                '%' . GeneralFunctions::cleanNumber($request->get('phone')) . '%'
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'phone', GeneralFunctions::cleanNumber($request->get('phone')));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'phone');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'phone')) {
                    $where[] = array(
                        'pabao_records.phone',
                        'like',
                        '%' . GeneralFunctions::cleanNumber(Filters::get(Auth::User()->id, 'pabao_records', 'phone')) . '%'
                    );
                }
            }
        }


        if ($request->get('mobile') && $request->get('mobile') != '') {
            $where[] = array(
                'pabao_records.mobile',
                'like',
                '%' . GeneralFunctions::cleanNumber($request->get('mobile')) . '%'
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'mobile', GeneralFunctions::cleanNumber($request->get('mobile')));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'mobile');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'mobile')) {
                    $where[] = array(
                        'pabao_records.mobile',
                        'like',
                        '%' . GeneralFunctions::cleanNumber(Filters::get(Auth::User()->id, 'pabao_records', 'mobile')) . '%'
                    );
                }
            }
        }

        if ($request->get('invoice_no') && $request->get('invoice_no') != '') {
            $where[] = array(
                'pabao_records.invoice_no',
                '=',
                $request->get('invoice_no')
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'invoice_no', $request->get('invoice_no'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'invoice_no');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'invoice_no')) {
                    $where[] = array(
                        'pabao_records.invoice_no',
                        '=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'invoice_no')
                    );
                }
            }
        }

        if ($request->get('issue_date_from') && $request->get('issue_date_from') != '') {
            $where[] = array(
                'pabao_records.issue_date',
                '>=',
                $request->get('issue_date_from') . ' 00:00:00'
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'issue_date_from', $request->get('issue_date_from'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'issue_date_from');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'issue_date_from')) {
                    $where[] = array(
                        'pabao_records.issue_date',
                        '>=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'issue_date_from') . ' 00:00:00'
                    );
                }
            }
        }

        if ($request->get('issue_date_to') && $request->get('issue_date_to') != '') {
            $where[] = array(
                'pabao_records.issue_date',
                '<=',
                $request->get('issue_date_to') . ' 23:59:59'
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'issue_date_to', $request->get('issue_date_to'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'issue_date_to');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'issue_date_to')) {
                    $where[] = array(
                        'pabao_records.issue_date',
                        '<=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'issue_date_to') . ' 23:59:59'
                    );
                }
            }
        }

        if ($request->get('total_amount') && $request->get('total_amount') != '') {
            $where[] = array(
                'pabao_records.total_amount',
                '=',
                $request->get('total_amount')
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'total_amount', $request->get('total_amount'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'total_amount');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'total_amount')) {
                    $where[] = array(
                        'pabao_records.total_amount',
                        '=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'total_amount')
                    );
                }
            }
        }

        if ($request->get('paid_amount') && $request->get('paid_amount') != '') {
            $where[] = array(
                'pabao_records.paid_amount',
                '=',
                $request->get('paid_amount')
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'paid_amount', $request->get('paid_amount'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'paid_amount');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'paid_amount')) {
                    $where[] = array(
                        'pabao_records.paid_amount',
                        '=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'paid_amount')
                    );
                }
            }
        }

        if ($request->get('outstanding_amount') && $request->get('outstanding_amount') != '') {
            $where[] = array(
                'pabao_records.outstanding_amount',
                '=',
                $request->get('outstanding_amount')
            );

            Filters::put(Auth::User()->id, 'pabao_records', 'outstanding_amount', $request->get('outstanding_amount'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'pabao_records', 'outstanding_amount');
            } else {
                if (Filters::get(Auth::User()->id, 'pabao_records', 'outstanding_amount')) {
                    $where[] = array(
                        'pabao_records.outstanding_amount',
                        '=',
                        Filters::get(Auth::User()->id, 'pabao_records', 'outstanding_amount')
                    );
                }
            }
        }

        $countQuery = PabaoRecords::where(function ($query) {
            $query->whereIn('pabao_records.location_id', ACL::getUserCentres());
            $query->orWhereNull('pabao_records.location_id');
        });


        if (count($where)) {
            $countQuery->where($where);
        }
        $iTotalRecords = $countQuery->count();


        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $records = array();
        $records["data"] = array();

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $resultQuery = PabaoRecords::where(function ($query) {
            $query->whereIn('pabao_records.location_id', ACL::getUserCentres());
            $query->orWhereNull('pabao_records.location_id');
        });


        if (count($where)) {
            $resultQuery->where($where);
        }
        $PabaoRecords = $resultQuery->select('*')
            ->limit($iDisplayLength)
            ->offset($iDisplayStart)
            ->orderBy($orderBy, $order)
            ->get();

        if ($PabaoRecords) {

            $Locations = Locations::getAllRecordsDictionary(Auth::User()->account_id);
            $amount_sum = 0;
            $index = 0;

            foreach ($PabaoRecords as $pabao_record) {

                $pabao_payment = PabaoRecords::find($pabao_record->id);

                $pabao_record_payment = PabaoRecordPayments::where('pabao_record_id', '=', $pabao_payment->id)->get();

                if (count($pabao_record_payment)>0) {

                    foreach ($pabao_record_payment as $pabao_record_payment) {
                        $amount_sum += $pabao_record_payment->amount;
                    }
                }
                $paid_amount = ($pabao_payment->paid_amount) + $amount_sum;

                $outstanding_amount = ($pabao_payment->outstanding_amount) - $amount_sum;

                $records["data"][$index] = array(
                    'location_id' => (array_key_exists($pabao_record->location_id, $Locations)) ? $Locations[$pabao_record->location_id]->name : 'N/A',
                    'client' => $pabao_record->client,
                    'phone' => $pabao_record->phone,
                    'mobile' => $pabao_record->mobile,
                    'invoice_no' => $pabao_record->invoice_no,
                    'issue_date' => Carbon::parse($pabao_record->issue_date)->format('F j,Y h:i A'),
                    'total_amount' => number_format($pabao_record->total_amount, 2),
                    'paid_amount' => number_format($paid_amount, 2),
                    'outstanding_amount' => number_format($outstanding_amount, 2),
                    'actions' => view('admin.pabao_records.actions', compact('pabao_record','outstanding_amount'))->render(),
                );

                $index++;
                $amount_sum = 0;
                $paid_amount = 0;
                $outstanding_amount= 0;
            }
        }

        if ($request->get('customActionType') && $request->get('customActionType') == "group_action") {
            $PabaoRecords = PabaoRecords::whereIn('id', $request->get('id'));
            if ($PabaoRecords) {
                $PabaoRecords->delete();
            }
            $records["customActionStatus"] = "OK"; // pass custom message(useful for getting status of group actions)
            $records["customActionMessage"] = "Records has been deleted successfully!"; // pass custom message(useful for getting status of group actions)
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    /**
     * Show PabaoRecord detail.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        if (!Gate::allows('pabao_records_manage')) {
            return abort(401);
        }
        $pabao_record = PabaoRecords::findOrFail($id);

        return view('admin.pabao_records.detailTo', compact('pabao_record'));
    }

    /**
     * Remove PabaoRecord from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('pabao_records_destroy')) {
            return abort(401);
        }
        $pabao_record = PabaoRecords::findOrFail($id);
        $pabao_record->delete();

        flash('Record has been deleted successfully.')->success()->important();

        return redirect()->route('admin.pabao_records.index');
    }

    /**
     * Store PabaoRecord Status.
     *
     * @param Request $request
     */
    public function importPabaoRecords(Request $request)
    {
        if (!Gate::allows('pabao_records_import')) {
            flash('You are not authorized to access this resource.')->error()->important();
            return redirect()->route('admin.pabao_records.index');
        }

        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $locations->prepend('Select a Centre', '');

        return view('admin.pabao_records.import', compact('locations'));
    }

    /**
     * Update PabaoRecord in storage.
     *
     * @param  \App\Http\Requests\Admin\FileUploadPabaoRecordsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function uploadPabaoRecords(FileUploadPabaoRecordsRequest $request)
    {
        if (!Gate::allows('pabao_records_import')) {
            flash('You are not authorized to access this resource.')->error()->important();
            return redirect()->route('admin.pabao_records.index');
        }

        if ($request->hasfile('pabao_records_file')) {
            // Check if directory not exists then create it
            $dir = public_path(DIRECTORY_SEPARATOR . 'pabaodata');
            if (!File::isDirectory($dir)) {
                // path does not exist so create directory
                File::makeDirectory($dir, 777, true, true);
                File::put($dir . DIRECTORY_SEPARATOR . 'index.html', 'Direct access is forbidden');
            }

            $File = $request->file('pabao_records_file');

            // Store File Information
            $name = str_replace('.' . $File->getClientOriginalExtension(), '', $File->getClientOriginalName());
            $ext = $File->getClientOriginalExtension();
            $full_name = $File->getClientOriginalName();
            $full_name_new = $name . '-' . rand(11111111, 99999999) . '.' . $ext;
            $full_name_new = str_replace(' ', '', trim($full_name_new));

            $File->move($dir, $full_name_new);

            // Read File and dump data
            if ('csv' == $ext) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $SpreadSheet = $reader->load($dir . DIRECTORY_SEPARATOR . $full_name_new);
            $SheetData = $SpreadSheet->getActiveSheet()->toArray(null, true, true, true);

            if (count($SheetData)) {

                if (
                    isset($SheetData[1])
                    && (
                        trim(strtolower($SheetData[1]['A'])) == 'client' &&
                        trim(strtolower($SheetData[1]['B'])) == 'invoice no.' &&
                        trim(strtolower($SheetData[1]['C'])) == 'issue date' &&
                        trim(strtolower($SheetData[1]['D'])) == 'employee' &&
                        trim(strtolower($SheetData[1]['E'])) == 'total amount' &&
                        trim(strtolower($SheetData[1]['F'])) == 'paid amount' &&
                        trim(strtolower($SheetData[1]['G'])) == 'outstanding amount'
                    )
                ) {

                    /*
                     * This array will contain all records
                     */
                    $piplined_records = array();

                    // Iterate over the data
                    foreach ($SheetData as $SingleRow) {
                        // Provided Sheet columns should match
                        if (
                            trim(strtolower($SingleRow['A'])) == 'client' &&
                            trim(strtolower($SingleRow['B'])) == 'invoice no.' &&
                            trim(strtolower($SingleRow['C'])) == 'issue date' &&
                            trim(strtolower($SingleRow['D'])) == 'employee' &&
                            trim(strtolower($SingleRow['E'])) == 'total amount' &&
                            trim(strtolower($SingleRow['F'])) == 'paid amount' &&
                            trim(strtolower($SingleRow['G'])) == 'outstanding amount'

                        ) {
                            // Row contains headers so ignore this line
                            continue;
                        }

                        if (
                            trim(strtolower($SingleRow['B'])) == 'total'
                        ) {
                            /*
                             * If Total field is provided skip this
                             */
                            continue;
                        }

                        // Process Phone Number
//                        $piplined_records[] = GeneralFunctions::cleanNumber(trim($SingleRow['C']));

                        $piplined_records[] = array(
                            'client' => $SingleRow['A'],
                            'invoice_no' => $SingleRow['B'],
                            'issue_date' => date('Y-m-d', strtotime($SingleRow['C'])),
                            'employee' => $SingleRow['D'],
                            'total_amount' => $SingleRow['E'],
                            'paid_amount' => $SingleRow['F'],
                            'outstanding_amount' => $SingleRow['G'],
                            'total_spend' => $SingleRow['H'],
                            'total_visits' => $SingleRow['I'],
                            'last_visit_days_ago' => $SingleRow['J'],
                            'new_client' => $SingleRow['K'],
                            'patient_id' => $SingleRow['L'],
                            'first_name' => $SingleRow['M'],
                            'last_name' => $SingleRow['N'],
                            'last_modified' => $SingleRow['O'],
                            'active' => $SingleRow['P'],
                            'country' => $SingleRow['Q'],
                            'salutation' => $SingleRow['R'],
                            'address_1' => $SingleRow['S'],
                            'address_2' => $SingleRow['T'],
                            'post_code' => $SingleRow['U'],
                            'mobile' => ($SingleRow['V']) ? GeneralFunctions::cleanNumber(trim($SingleRow['V'])) : null,
                            'phone' => ($SingleRow['W']) ? GeneralFunctions::cleanNumber(trim($SingleRow['W'])) : null,
                            'town' => $SingleRow['X'],
                            'full_address' => $SingleRow['Y'],
                            'gender' => $SingleRow['Z'],
                            'email' => $SingleRow['AA'],
                            'date_of_birth' => ($SingleRow['AB'] != '01/01/1970') ? date('Y-m-d', strtotime($SingleRow['AB'])) : null,
                            'privacy_policy' => $SingleRow['AC'],
                            'marketing_optin_email' => $SingleRow['AD'],
                            'marketing_optin_sms' => $SingleRow['AE'],
                            'marketing_optin_newsletter' => $SingleRow['AF'],
                            'marketing_source' => $SingleRow['AG'],
                            'age' => $SingleRow['AH'],
                            'insurer_name' => $SingleRow['AI'],
                            'contract_client' => $SingleRow['AJ'],
                            'appointments_attended_total' => $SingleRow['AK'],
                            'appointments_attended' => $SingleRow['AL'],
                            'online_bookings' => $SingleRow['AM'],
                            'appointments_dna' => $SingleRow['AN'],
                            'appointments_rescheduled' => $SingleRow['AO'],
                            'appointments_date_first' => ($SingleRow['AP'] != '01/01/1970') ? date('Y-m-d', strtotime($SingleRow['AP'])) : null,
                            'appointments_date_last' => ($SingleRow['AQ'] != '01/01/1970') ? date('Y-m-d', strtotime($SingleRow['AQ'])) : null,
                            'outstanding_balance' => $SingleRow['AR'],
                            'amount_balance' => $SingleRow['AS'],
                            'first_booking_with' => $SingleRow['AT'],
                            'first_booking_service' => $SingleRow['AU'],
                            'membership_number' => $SingleRow['AV'],
                            'future_booking' => $SingleRow['AW'],
                            'future_booking_date' => $SingleRow['AX'],
                            'next_appointment' => $SingleRow['AY'],
                            'client_created_by' => $SingleRow['AZ'],
                            'episode_id' => $SingleRow['BA'],
                            'client_sys_id' => $SingleRow['BB'],
                            'location' => $SingleRow['BC'],
                            'location_id' => $request->get('location_id'),
                        );
                    }

                    // If Get some recors insert them now
                    if (count($piplined_records)) {
                        PabaoRecords::insert($piplined_records);
                    }

                    // Invalid data is provided
                    flash('Pabau Records has been imported. Created: ' . count($piplined_records))->success()->important();

                    return redirect()->route('admin.pabao_records.index');
                } else {
                    flash('Invalid data provided.')->error()->important();
                }
            } else {
                flash('No input file specified..')->error()->important();
            }

            return redirect()->route('admin.pabao_records.import');
        }
    }

    /**
     * Create a add payment of PabaoRecord.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if (!Gate::allows('pabao_records_payment')) {
            return abort(401);
        }
        $amount_sum = 0;

        $pabao_payment = PabaoRecords::find($id);

        $totol_amount = $pabao_payment->total_amount;

        $pabao_record_payment = PabaoRecordPayments::where('pabao_record_id', '=', $pabao_payment->id)->get();

        if (count($pabao_record_payment)>0) {
            foreach ($pabao_record_payment as $pabao_record_payment) {
                $amount_sum += $pabao_record_payment->amount;
            }
        }

        $paid_amount = ($pabao_payment->paid_amount) + $amount_sum;

        $outstanding_amount = $pabao_payment->outstanding_amount - $amount_sum;

        return view('admin.pabao_records.create', compact('id', 'totol_amount','paid_amount','outstanding_amount'));
    }

    /**
     * Store a newly created Permission in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if(PabaoRecordPayments::CreateRecord($request,Auth::User()->account_id,Auth::User()->id)) {
            flash('Record has been created successfully.')->success()->important();

            return response()->json(array(
                'status' => 1,
                'message' => 'Record has been created successfully.',
            ));
        } else {
            return response()->json(array(
                'status' => 0,
                'message' => 'Something went wrong, please try again later.',
            ));
        }
    }

    /**
     * detail of payment of PabaoRecord.
     *
     * @return \Illuminate\Http\Response
     */
    public function detailpayment($id){

        if (!Gate::allows('pabao_records_detail')) {
            return abort(401);
        }
        $amount_sum = 0;

        $pabao_payment = PabaoRecords::find($id);

        $totol_amount = $pabao_payment->total_amount;

        $payment_pabau_history = $pabao_record_payment = PabaoRecordPayments::where('pabao_record_id', '=', $pabao_payment->id)->get();

        if (count($pabao_record_payment)>0) {
            foreach ($pabao_record_payment as $pabao_record_payment) {
                $amount_sum += $pabao_record_payment->amount;
            }
        }

        $paid_amount = ($pabao_payment->paid_amount) + $amount_sum;

        $outstanding_amount = ($pabao_payment->outstanding_amount) - $amount_sum;

        return view('admin.pabao_records.detailPayment', compact('id', 'totol_amount','paid_amount','outstanding_amount','payment_pabau_history','pabao_payment'));
    }

    /**
     * detete of payment of PabaoRecord.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteRecord($id){

        if (!Gate::allows('pabao_records_destroy')) {
            return abort(401);
        }
        if(PabaoRecordPayments::DeleteRecord($id)) {
            flash('Record has been delete successfully.')->success()->important();
        } else {
            flash('Something went wrong, please try again later.')->success()->important();
        }
        return redirect()->route('admin.pabao_records.index');
    }

    public function pabao_pdf($id){
        if (!Gate::allows('pabao_records_detail')){
            return abort(404);
        }

        $amount_sum = 0;

        $pabao_payment = PabaoRecords::find($id);

        $totol_amount = $pabao_payment->total_amount;

        $payment_pabau_history = $pabao_record_payment = PabaoRecordPayments::where('pabao_record_id', '=', $pabao_payment->id)->get();

        if (count($pabao_record_payment)>0) {
            foreach ($pabao_record_payment as $pabao_record_payment) {
                $amount_sum += $pabao_record_payment->amount;
            }
        }

        $paid_amount = ($pabao_payment->paid_amount) + $amount_sum;

        $outstanding_amount = ($pabao_payment->outstanding_amount) - $amount_sum;

        $location_info = Locations::find( $pabao_payment->location_id);

        $company_phone_number = Settings::where('slug', '=', 'sys-headoffice')->first();

        $content = view('admin.pabao_records.pabao_pdf', compact('id', 'totol_amount','paid_amount','outstanding_amount','payment_pabau_history','pabao_payment','location_info','company_phone_number'));
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($content);
        return $pdf->stream('admin.pabao_records.pabao_pdf.pdf');

    }

}
