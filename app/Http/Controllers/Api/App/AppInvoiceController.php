<?php

namespace App\Http\Controllers\Api\App;

use App\Helpers\Widgets\AppointmentCheckesWidget;
use App\InvoiceScanLog;
use App\Jobs\IndexSingleAppointmentJob;
use App\Models\Accounts;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\Discounts;
use App\Models\Invoices;
use App\Models\InvoiceStatuses;
use App\Models\Locations;
use App\Models\Resources;
use App\Models\Services;
use App\Models\Settings;
use App\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AppInvoiceController extends Controller
{

    public function displayInvoice(Request $request)
    {
        // Validation check & return error if found
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->validationFailure($error);
        } else {
            // We write this query to get Invoice & its details
            $Invoiceinfo = DB::table('invoices')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->where('invoices.id', '=', $id)
                ->select('invoices.*',
                    'invoice_details.discount_type',
                    'invoice_details.discount_price',
                    'invoice_details.service_price',
                    'invoice_details.net_amount',
                    'invoice_details.service_id',
                    'invoice_details.discount_id',
                    'invoice_details.package_id',
                    'invoice_details.invoice_id',
                    'invoice_details.tax_exclusive_serviceprice',
                    'invoice_details.tax_percenatage',
                    'invoice_details.tax_price',
                    'invoice_details.tax_including_price',
                    'invoice_details.is_app',
                    'invoice_details.is_exclusive'
                )
                ->first();

            // Here we check services patient account info regard invoice
            if($Invoiceinfo){
                $location_info = Locations::find($Invoiceinfo->location_id);
                $invoicestatus = InvoiceStatuses::find($Invoiceinfo->invoice_status_id);
                if ($Invoiceinfo->discount_id) {
                    $discount = Discounts::find($Invoiceinfo->discount_id);
                } else {
                    $discount = null;
                }
                $service = Services::find($Invoiceinfo->service_id);
                $patient = User::find($Invoiceinfo->patient_id);
                $account = Accounts::find($Invoiceinfo->account_id);
                $company_phone_number = Settings::where('slug', '=', 'sys-headoffice')->first();

                if ($Invoiceinfo->is_exclusive == '0') {
                    $Invoiceinfo->price = number_format(($Invoiceinfo->service_price) - ($Invoiceinfo->tax_price));
                } else if ($Invoiceinfo->is_exclusive == '1') {
                    $Invoiceinfo->price = number_format($Invoiceinfo->service_price);
                }
                $Invoiceinfo->created_at = Carbon::parse($Invoiceinfo->created_at)->format('F j,Y');
                $sub_total = 0;
                if ($Invoiceinfo->is_exclusive == '0') {
                    if ($Invoiceinfo->discount_price == null) {
                        $sub_total = number_format(($Invoiceinfo->service_price) - ($Invoiceinfo->tax_price));
                    } else {
                        $sub_total = number_format($Invoiceinfo->tax_exclusive_serviceprice);
                    }
                } else if ($Invoiceinfo->is_exclusive == '1') {
                    $sub_total = number_format($Invoiceinfo->tax_exclusive_serviceprice);
                }

                // Here we store invoice logs which pdf scan & which user try to scan and when invoice scan
                $this->invoice_logs(Auth::id(), $id);

                // This is final response which is send to when someone try to scan the qrcode and this will return date
                return response()->json([
                    'id' => $Invoiceinfo->id ?? "",
                    'status' => $Invoiceinfo->status ?? 0,
                    'is_app' => $Invoiceinfo->is_app,
                    'invoice_slug' => $invoicestatus->slug ?? "",
                    'image_src' => asset('centre_logo/') . '/' . $location_info->image_src ?? "",
                    'created_at' => Carbon::parse($Invoiceinfo->created_at)->format('F j,Y') ?? "",
                    'client_customer_iD' => $patient->id ?? "",
                    'client_name' => $patient->name ?? "",
                    'client_email' => $patient->email ?? "",
                    'client_contact' => $patient->phone ?? "",
                    'company_name' => $account->name ?? "",
                    'company_contact' => $company_phone_number->data ?? "",
                    'company_email' => $account->email ?? "",
                    'clinic_name' => $location_info->name ?? "",
                    'clinic_contact_no' => $location_info->fdo_phone ?? "",
                    'clinic_address' => $location_info->address ?? "",
                    'clinic_ntn' => $location_info->ntn ?? "",
                    'clinic_stn' => $location_info->stn ?? "",
                    'service_name' => $service->name ?? "",
                    'service_price' => $Invoiceinfo->is_exclusive == '0' ? number_format(($Invoiceinfo->service_price) - ($Invoiceinfo->tax_price)) : number_format($Invoiceinfo->service_price),
                    'discount_name' => $discount != null ? $discount->name : "-",
                    'discount_type' => $Invoiceinfo->discount_type != null ? $Invoiceinfo->discount_type : "-",
                    'discount_price' => $Invoiceinfo->discount_price != null ? number_format($Invoiceinfo->discount_price) : "-",
                    'sub_total' => $sub_total  ?? "",
                    'tax_percentage' => $Invoiceinfo->tax_percenatage  ?? "",
                    'tax_price' => $Invoiceinfo->tax_price  ?? "",
                    'total_tax_including_price' => number_format($Invoiceinfo->tax_including_price)  ?? "",
                    'total_price' => number_format($Invoiceinfo->total_price)  ?? "",
                    'invoice_status' => $Invoiceinfo->status == 0 ? "Not Verify" : "Verify",
                ]);
            }
            else{
                $this->invoice_logs(Auth::id(), $id, "Scan", "No");
                return response([
                    'success' => false,
                    'message' => "Invoice not found",
                ], 404);
            }
        }


    }

    public function updateInvoiceStatus($id)
    {
        // This function verify invoice & also store data into invoice scan logs table
        $invoice = Invoices::find($id);
        if ($invoice) {
            $invoice->status = 1;
            $invoice->save();

            $this->invoice_logs(Auth::id(), $id, "Verify");
            return response([
                'success' => true,
                'message' => "Invoice verified",
            ], 200);
        } else {
            $this->invoice_logs(Auth::id(), $id, "Verify", "No");
            return response([
                'success' => false,
                'message' => "Invoice not found",
            ], 404);
        }
    }

    public function invoice_logs($user_id = null, $invoice_id = null, $status = "Scan", $invoice_found = "Yes")
    {
        $invoice_scan_logs =new InvoiceScanLog();
        $invoice_scan_logs->user_id = $user_id;
        $invoice_scan_logs->invoice_id = $invoice_id;
        $invoice_scan_logs->action = $status;
        $invoice_scan_logs->invoice_found = $invoice_found;
        $invoice_scan_logs->save();
    }

    public function checkAndSaveAppointments(Request $request)
    {

        $appointment_checkes = AppointmentCheckesWidget::AppointmentConsultancyCheckes($request);
        if ($appointment_checkes['status']) {
            $doctor_check_availability = Resources::checkDoctorAvailbility($request);
            if (
                $request->get("id") &&
                $request->get("start") &&
                $request->get("doctor_id") &&
                $request->get("end")
            ) {
                if ($doctor_check_availability) {
                    // Appointment Data
                    $data = $request->all();

                    $appointment = Appointments::findOrFail($request->get('id'));

                    $data['first_scheduled_count'] = $appointment->first_scheduled_count;
                    $data['scheduled_at_count'] = $appointment->scheduled_at_count;

                    if ($appointment->appointment_type_id = Config::get('constants.appointment_type_consultancy')) {
                        $response = Resources::getDoctorRotaHasDay($request->get("start"), $appointment->doctor_id);
                        if (isset($response['resource_id']) && $response['resource_id']) {
                            $data['resource_id'] = $response['resource_id'];
                        }
                        if (isset($response['resource_has_rota_day_id']) && $response['resource_has_rota_day_id']) {
                            $data['resource_has_rota_day_id'] = $response['resource_has_rota_day_id'];
                        }
                    }

                    $invoicestatus = InvoiceStatuses::where('slug', '=', 'paid')->first();

                    $invoice = Invoices::where([
                        ['appointment_id', '=', $appointment->id],
                        ['invoice_status_id', '=', $invoicestatus->id]
                    ])->get();
                    if (count($invoice) > 0) {
                        return response()->json(array(
                            'status' => 0,
                            "message" => trans("global.appointments.invoice_paid_message")
                        ), 200);
                    }

                    $record = Appointments::updateRecordAPI($request->get("id"), $data, 1);
                    if ($record) {
                        /*
                         * Set Appointment Status 'pending' and set send message flag
                         */
                        $appointment_status = AppointmentStatuses::getADefaultStatusOnly(1);
                        if ($appointment_status) {
                            $record->update(array(
                                'appointment_status_id' => $appointment_status->id,
                                'base_appointment_status_id' => $appointment_status->id,
                                'appointment_status_allow_message' => $appointment_status->allow_message,
                                'send_message' => 1, // Set flag 1 to send message on cron job
                            ));
                        }

                        /**
                         * Dispatch Elastic Search Index
                         */
//                        $this->dispatch(
//                            new IndexSingleAppointmentJob([
//                                'account_id' => 1,
//                                'appointment_id' => $appointment->id
//                            ])
//                        );

                        return response()->json(array(
                            'status' => 1,
                            "message" => "Event Updated Successfully"
                        ));
                    }
                }
                else {
                    return response()->json(array(
                        'status' => 0,
                        "message" => "Doctor is not available"
                    ), 200);
                }
            } else {
                return response()->json(array(
                    'status' => 0,
                    "message" => "Invalid paramters"
                ), 200);
            }
        } else {
            return response()->json(array(
                'status' => 0,
                "message" => $appointment_checkes['message']
            ));
        }
    }

}
