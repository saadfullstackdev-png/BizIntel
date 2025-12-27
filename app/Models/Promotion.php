<?php

namespace App\Models;

use App\Helpers\Filters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;

class Promotion extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['code', 'user_id', 'discount_id', 'discount_slug', 'taken', 'use', 'account_id', 'created_at', 'updated_at', 'deleted_at'];

    protected static $_fillable = ['code', 'user_id', 'discount_id', 'discount_slug', 'taken', 'use', 'account_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $table = 'promotions';

    protected static $_table = 'promotions';

    /**
     * Get the User for Promotion.
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }

    /**
     * Get the discount for Promotion.
     */
    public function discount()
    {
        return $this->belongsTo('App\Models\Discounts')->withTrashed();
    }

    static public function createRecord($data)
    {
        $record = self::create($data);

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }

    /**
     * Update Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function updateRecord($id, $request, $account_id)
    {
        $old_data = (Promotion::find($id))->toArray();

        $data = $request->all();

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        AuditTrails::EditEventLogger(self::$_table, 'edit', $data, self::$_fillable, $old_data, $id);

        return $record;
    }
    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false,$id = false, $apply_filter = false, $filename)
    {
        $where = self::promotion_filters( $request, $account_id , $id , $apply_filter, $filename );
        return self::where($where)->count();
    }

    /**
     * Get Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $iDisplayStart Start Index
     * @param (int) $iDisplayLength Total Records Length
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false,$id = false, $apply_filter = false, $filename)
    {
        $where = Self::promotion_filters($request, $account_id , $id , $apply_filter, $filename );
        return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
    }

    /**
     * Get filters
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     * @param (boolean) $apply_filter
     * @return (mixed)
     */
    static public function promotion_filters($request , $account_id , $id, $apply_filter, $filename )
    {
        $where = array();

        if($id != false){
            $where[] = array(
                'user_id',
                '=',
                $id
            );
            Filters::put(Auth::user()->id, $filename, 'id', $id);
        } else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id, $filename, 'id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'id')){
                    $where[] = array(
                        'user_id',
                        '=',
                        Filters::get(Auth::user()->id,$filename, 'id')
                    );
                }
            }
        }

        if ($request->get('patient_id') && $request->get('patient_id') != '' ) {
            $where[] = array(
                'user_id',
                '=',
                $request->get('patient_id')
            );
            Filters::put(Auth::User()->id, $filename, 'patient_id', $request->get('patient_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, $filename, 'patient_id');
            } else {
                if (Filters::get(Auth::User()->id, $filename, 'patient_id')) {
                    $where[] = array(
                        'user_id',
                        '=',
                        Filters::get(Auth::User()->id, $filename, 'patient_id')
                    );
                }
            }
        }

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::user()->id , $filename, 'account_id', $account_id ) ;
        } else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id ,$filename, 'account_id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'account_id')){
                    $where[] = array(
                        'invoices.account_id',
                        '=',
                        Filters::get(Auth::user()->id ,$filename, 'account_id')
                    );
                }
            }
        }

        if ( $request->get('discount_id')){
            $where[] = array(
                'discount_id',
                '=',
                $request->get('discount_id')
            );
            Filters::put(Auth::user()->id, $filename, 'discount_id', $request->get('discount_id'));
        } else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id, $filename, 'discount_id');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'discount_id')){
                    $where[] = array(
                        'discount_id',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'discount_id')
                    );
                }
            }
        }

        if ($request->get('code')) {
            $where[] = array(
                'code',
                '=',
                $request->get('code')
            );
            Filters::put(Auth::user()->id , $filename, 'code', $request->get('code')) ;
        }
        else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id ,$filename, 'code');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'code')){
                    $where[] = array(
                        'code',
                        '=',
                        Filters::get(Auth::user()->id ,$filename, 'code')
                    );
                }
            }
        }
        if ($request->get('use')) {
            $where[] = array(
                'use',
                '=',
                $request->get('use')
            );
            Filters::put(Auth::user()->id , $filename, 'use', $request->get('use')) ;
        }
        else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id ,$filename, 'use');
            } else {
                if (Filters::get(Auth::user()->id, $filename, 'use')){
                    $where[] = array(
                        'use',
                        '=',
                        Filters::get(Auth::user()->id ,$filename, 'use')
                    );
                }
            }
        }


        if ($request->get('taken')){
            $where[] = array(
                'taken',
                '=',
                $request->get('taken')
            );
            Filters::put(Auth::user()->id, $filename, 'taken', $request->get('taken'));
        } else {
            if ($apply_filter){
                Filters::forget(Auth::user()->id , $filename, 'taken');
            } else {
                if (Filters::get(Auth::user()->id ,$filename, 'taken')){
                    $where[] = array(
                        'taken',
                        '=',
                        Filters::get(Auth::user()->id, $filename, 'taken')
                    );
                }
            }
        }
        return $where ;
    }


}
