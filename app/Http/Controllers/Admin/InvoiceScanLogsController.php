<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\InvoiceScanLog;
use App\Models\UserLoginLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceScanLogsController extends Controller
{
    public function index()
    {
        return view('admin.invoice_scan_logs.index');
    }

    public function datatable(Request $request)
    {

        $records = array();
        $records["data"] = array();
        // Get Total Records
        $iTotalRecords = InvoiceScanLog::count();
        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $invoiceScanLogs = InvoiceScanLog::getRecords($request, $iDisplayStart, $iDisplayLength);

        if ($invoiceScanLogs) {
            foreach ($invoiceScanLogs as  $invoiceScanLog) {
                $records["data"][] = array(
                    'user_id' => $invoiceScanLog->user->name??"",
                    'invoice_id' => $invoiceScanLog->invoice_id,
                    'action' => $invoiceScanLog->action,
                    'invoice_found' => $invoiceScanLog->invoice_found,
                    'created_at' => $invoiceScanLog->created_at ? Carbon::parse($invoiceScanLog->created_at)->format('F j,Y h:i A') : '',
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;
        return response()->json($records);
    }
}
