<?php

namespace App;

use App\Models\Invoices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InvoiceScanLog extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_id',
        'action',
        'invoice_found',
        'inv_qr',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoices::class);
    }

    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength)
    {
        $orderBy = 'created_at';
        $order = 'desc';
        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }
        return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy, $order)->get();
    }

}
