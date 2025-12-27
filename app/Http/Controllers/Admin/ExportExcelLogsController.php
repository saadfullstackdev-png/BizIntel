<?php

namespace App\Http\Controllers\Admin;

use App\Models\ExportExcelLogs;
use App\Models\ExportLogs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportExcelLogsController extends Controller
{
    public function index()
    {
        if (! Gate::allows('export_excel_manage')) {
            return abort(401);
        }
        return view('admin.export_excel_logs.index');
    }
    /**
     * Display a listing of Lead_statuse.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function datatable(Request $request)
    {
        $records = array();
        $records["data"] = array();


        $export_log = new ExportExcelLogs();
        if ( $request->name ){
            $name = $request->name;
            $export_log = $export_log->whereHas('user', function ($query) use ($name){
                $query->where('name', 'like', "%${name}%");
            });
        }
        if ($request->exported_model){
            $export_log = $export_log->where('exported_model', config('constants.exported_module.'.$request->exported_model));
        }

        // Get Total Records
        $iTotalRecords = $export_log->count();



        $iDisplayLength = intval($request->get('length'));
        $iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
        $iDisplayStart = intval($request->get('start'));
        $sEcho = intval($request->get('draw'));

        $export_logs = $export_log->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('id','desc')->get();

        if($export_logs) {
            foreach($export_logs as $export_log) {
                $records["data"][] = array(
                    'id' => '<label class="mt-checkbox mt-checkbox-single mt-checkbox-outline"><input name="id[]" type="checkbox" class="checkboxes" value="'.$export_log->id.'"/><span></span></label>',
                    'name' => $export_log->user->name,
                    'email' => $export_log->user->email,
                    'phone' => $export_log->user->phone,
                    'exported_model' => ucfirst($export_log->exported_model),
                    'created_at' => Carbon::parse($export_log->created_at)->format('F j,Y h:i A'),
                    'actions' => view('admin.export_excel_logs.actions', compact('export_log'))->render(),
                );
            }
        }

        $records["draw"] = $sEcho;
        $records["recordsTotal"] = $iTotalRecords;
        $records["recordsFiltered"] = $iTotalRecords;

        return response()->json($records);
    }

    public function download_file(Request $request)
    {
        if (! Gate::allows('export_excel_manage')) {
            return abort(401);
        }
        $export_log = ExportExcelLogs::findOrFail($request->export_log_id);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . 'General Report' . '.xlsx"'); /*-- $filename is  xsl filename ---*/
        header('Cache-Control: max-age=0');
        $file_path = storage_path() . $export_log->excel_path;
        $SpreadSheet = IOFactory::load($file_path);
        $excel = new Xlsx($SpreadSheet);
        $excel->save('php://output');
    }
}
