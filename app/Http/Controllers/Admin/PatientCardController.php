<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Helpers\GeneralFunctions;
use App\Helpers\NodesTree;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\AppointmentTypes;
use App\Models\Cities;
use App\Models\Doctors;
use App\Models\Documents;
use App\Models\Leads;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Cards;
use App\Models\Patients;
use App\Models\Services;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use DB, Validator, Config;
use PHPUnit\Util\Filter;
use Yajra\DataTables\Facades\DataTables;
use App\Models\CardSubscription;
use App\Models\CardSubscriptionDetail;
use App\Models\SubscriptionCharge;
use App\Models\PurchasedService;
use App\Models\WalletMeta;
use App\Models\Wallet;
use DateTime;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PatientCardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $subscriptions = CardSubscription::with('patient')
                ->leftJoin('locations', 'card_subscriptions.location', '=', 'locations.id')
                ->leftJoin('card_subscription_details', 'card_subscriptions.id', '=', 'card_subscription_details.subscription_card_id')
                ->select([
                    'card_subscriptions.*',
                    'card_subscription_details.amount',
                    'card_subscription_details.created_at',
                    'card_subscription_details.updated_at',
                    'locations.name',
                ]);

            // Apply date filters if provided
            if ($request->has('user_location_filter') && !empty($request->user_location_filter)) {
                $subscriptions->where('card_subscriptions.location', $request->user_location_filter);
            }
            if ($request->has('from_date') && !empty($request->from_date)) {
                $subscriptions->whereDate('card_subscription_details.created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date') && !empty($request->to_date)) {
                $subscriptions->whereDate('card_subscription_details.created_at', '<=', $request->to_date);
            }

            return Datatables::eloquent($subscriptions)
                ->addIndexColumn()
                ->addColumn('patient.name', function ($subscription) {
                    return $subscription->patient ? $subscription->patient->name : '-';
                })
                ->addColumn('patient.phone', function ($subscription) {
                    return $subscription->patient ? $subscription->patient->phone : '-';
                })
                ->addColumn('locations.name', function ($subscription) {
                    return $subscription->name ? $subscription->name : '-';
                })
                ->make(true);
        }
        $userLocation = Locations::leftJoin('cities', 'cities.id', '=', 'locations.city_id')
        ->whereIn('locations.id',auth()->user()->user_has_locations()->pluck('location_id')->toArray())->select('locations.id','locations.name as location_name','cities.name as city_name')->get();
        $subscriptions = CardSubscription::with('patient')->get();
        return view('admin.patients.card', compact('subscriptions', 'userLocation'));
    }

    public function create()
    {
        return view('admin.patients.cardcreate');
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer',
            'user_location' => 'required|integer',
        ]);

        $date = new DateTime();
        $subscription = CardSubscription::where('patient_id', $request->patient_id)->first();
        if (!$subscription) {
            $subscription = CardSubscription::create([
                'card_number' => round(microtime(true) * 1000) . $request->patient_id,
                'patient_id' => $request->patient_id,
                'location' => $request->user_location,
                'is_app' => 0,
                'subscription_date' => now(),
                'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
            ]);
        } else {
            $subscription->update([
                'location' => $request->user_location,
                'subscription_date' => now(),
                'is_app' => 0,
                'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                'is_active' => 1
            ]);
        }
        if (CardSubscription::count() <=100) {
            $wallet = Wallet::firstOrCreate(
                ['patient_id' => $request->patient_id],
                ['account_id' => 1] 
            );
            $wallet_id = $wallet->id;
            WalletMeta::create([
                'cash_flow' => 'in',
                'cash_amount' => 5000,
                'patient_id' => $request->patient_id,
                'wallet_id' => $wallet_id,
            ]);
        }
        $charges= SubscriptionCharge::where('account_id',auth()->user()->account_id)->first();
        $charges= $charges?  $charges->amount : 0;

            CardSubscriptionDetail::create([
                'subscription_card_id' => $subscription->id,
                'amount' =>  $charges,
                'account_id' => auth()->user()->account_id,
            ]);

        return response()->json(['success' => 'Card Subscription created successfully!']);
    }


    public function show($id)
    {
        $subscription = CardSubscription::findOrFail($id);
        return view('admin.card_subscription.show', compact('subscription'));
    }

    public function edit($id)
    {
        $subscription = CardSubscription::with('patient')->findOrFail($id);
        // dd($subscription);
        return response()->json($subscription);
        // return view('admin.patients.cardedit', compact('subscription'));
    }
    
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'patient_id' => 'required|integer',
         ]);
         $date = new DateTime();
        $subscription = CardSubscription::where('patient_id', $request->patient_id)->first();
             if (!$subscription) {
                 CardSubscription::create([
                     'card_number' => round(microtime(true) * 1000) . $request->patient_id,
                     'is_active' => 1,
                     'patient_id' => $request->patient_id,
                     'subscription_date' => now(),
                     'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                 ]);
             } else {
                 $subscription->update([
                     'subscription_date' => now(),
                     'expiry_date' => $date->modify('+1 year')->format('Y-m-d H:i:s'),
                     'is_active' => $request->is_active,
                 ]);
             }
            $charges= SubscriptionCharge::where('account_id',auth()->user()->account_id)->first();
            $charges= $charges?  $charges->amount : 0;
 
             CardSubscriptionDetail::create([
                 'subscription_card_id' => $subscription->id,
                 'amount' =>  $charges,
                 'account_id' => auth()->user()->account_id,
             ]);   

        // return redirect()->route('admin.card-subscription.index')->with('success', 'Card Subscription updated successfully.');
        return response()->json(['success' => 'Card Subscription updated successfully!']);
    }


    public function destroy($id)
    {
        $subscription = CardSubscription::findOrFail($id);
        $subscription->delete();

        return redirect()->route('admin.card-subscription.index')->with('success', 'Card Subscription deleted successfully.');
    }

    public function processSubscription($transection)
    {
        // Initialize a DateTime object for date calculations        
    }

    public function purchased_serivces(Request $request)
    {
        if ($request->ajax()) {
            $subscriptions = PurchasedService::leftjoin('services','services.id','=','purchased_services.service_id')
            ->leftjoin('locations','locations.id','=','purchased_services.location_id')
            ->leftjoin('users','users.id','=','purchased_services.patient_id')->select('services.name as service_name','locations.name as location_name','users.name as patient_name','purchased_services.price as purchased_services_price','purchased_services.is_consumed','purchased_services.created_at','users.phone as patient_phone');
            return Datatables::eloquent($subscriptions)->addIndexColumn()->make(true);
        }
        return view('admin.patients.purchasedServices');
    }
    
    public function export(Request $request)
    {
        \Log::info('Export method called with format: ' . $request->get('format'));
        try {
            $format = $request->get('format', 'excel');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            // Get the data with the same filters as your DataTable
            $query = CardSubscription::with('patient')
                ->leftJoin('card_subscription_details', 'card_subscriptions.id', '=', 'card_subscription_details.subscription_card_id')
                ->select([
                    'card_subscriptions.id',
                    'card_subscriptions.card_number',
                    'card_subscriptions.patient_id',
                    'card_subscriptions.subscription_date',
                    'card_subscriptions.expiry_date',
                    'card_subscriptions.is_active',
                    'card_subscription_details.amount',
                    'card_subscription_details.created_at as detail_created_at',
                    'card_subscription_details.updated_at as detail_updated_at',
                ]);

            // Apply date filters if provided - Fix the filter logic
            if ($fromDate) {
                $query->whereDate('card_subscription_details.created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('card_subscription_details.created_at', '<=', $toDate);
            }

            $subscriptions = $query->get();

            if ($format === 'excel') {
                return $this->exportToExcel($subscriptions, $fromDate, $toDate);
            } elseif ($format === 'pdf') {
                return $this->exportToPdf($subscriptions, $fromDate, $toDate);
            }

            return response()->json(['error' => 'Invalid format'], 400);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }

    private function exportToExcel($subscriptions, $fromDate = null, $toDate = null)
    {
        try {
            $filename = 'card_subscriptions_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setTitle('Card Subscriptions');
            
            // Add headers
            $headers = [
                'ID',
                'Card Number', 
                'Patient Name',
                'Patient Phone',
                'Subscription Date',
                'Expiry Date',
                'Created At',
                'Updated At',
                'Status',
                'Amount'
            ];
            
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            
            // Add data
            $row = 2;
            $totalAmount = 0;
            
            foreach ($subscriptions as $subscription) {
                $amount = $subscription->amount ?? 0;
                $totalAmount += $amount;
                $data = [
                    $subscription->id,
                    (string) $subscription->card_number, // Cast to string to ensure text format
                    $subscription->patient ? $subscription->patient->name : '-',
                    $subscription->patient ? $subscription->patient->phone : '-',
                    $subscription->subscription_date ? date('Y-m-d', strtotime($subscription->subscription_date)) : '-',
                    $subscription->expiry_date ? date('Y-m-d', strtotime($subscription->expiry_date)) : '-',
                    $subscription->detail_created_at ? date('Y-m-d', strtotime($subscription->detail_created_at)) : '-',
                    $subscription->detail_updated_at ? date('Y-m-d', strtotime($subscription->detail_updated_at)) : '-',
                    $subscription->is_active == 1 ? 'Active' : 'Inactive',
                    number_format($amount, 2)
                ];
                
                $sheet->fromArray($data, null, 'A' . $row);
                $row++;
            }
            
            // Add total row
            $totalData = ['', '', '', '', '', '', '', '', 'Total:', number_format($totalAmount, 2)];
            $sheet->fromArray($totalData, null, 'A' . $row);
            
            // Style the header row
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E2E2']
                ]
            ];
            $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
            
            // Style the total row
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F8F9FA']
                ]
            ]);
            
            // Auto-size columns
            foreach (range('A', 'J') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]);

        } catch (\Exception $e) {
            \Log::error('Excel Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'Excel export failed: ' . $e->getMessage()], 500);
        }
    }

    private function exportToPdf($subscriptions, $fromDate = null, $toDate = null)
    {
        try {
            $total = $subscriptions->sum('amount');
            
            $data = [
                'subscriptions' => $subscriptions,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'total' => $total
            ];

            $pdf = Pdf::loadView('admin.patients.card_subscriptions_pdf', $data);
            $pdf->setPaper('A4', 'landscape'); // Set to landscape for better table display
            
            $filename = 'card_subscriptions_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json(['error' => 'PDF export failed: ' . $e->getMessage()], 500);
        }
    }
}