<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;
use App\Helpers\ACL;
use App\Helpers\Filters;

class TermsAndPolicies extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'created_at', 'updated_at','deleted_at', 'active' ,'account_id'];

    protected static $_fillable = ['name', 'description'];

    protected $table = 'termsandpolicies';

    protected static $_table = 'termsandpolicies';

    /**
     * sent the city data to resource has rota.
     */
    // public function resourcehasrota()
    // {
    //     return $this->hasMany('App\Models\ResourceHasRota', 'city_id');
    // }

    /**
     * Get the Locations for City.
     */
    // public function locations()
    // {
    //     return $this->hasMany('App\Models\Locations', 'city_id');
    // }

    /**
     * Get the Region for City.
     */
    // public function region()
    // {
    //     return $this->belongsTo('App\Models\Regions', 'region_id');
    // }

    /**
     * Get the town of city.
     */
    // public function town()
    // {
    //     return $this->hasMany('App\Models\Towns', 'city_id', 'id');
    // }

    /**
     * Get the Active Locations for City.
     */
    // public function locationsActive()
    // {
    //     return $this->hasMany('App\Models\Locations', 'city_id')->where(['active' => 1]);
    // }

    /**
     * Get the doctors for City.
     */
    // public function doctors()
    // {
    //     return $this->hasMany('App\Models\Doctors', 'city_id');
    // }

    /**
     * Get the appointments for City.
     */
    // public function appointments()
    // {
    //     return $this->hasMany('App\Models\Appointments', 'city_id');
    // }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveSorted($termsandpolicyId = false)
    {
        if ($termsandpolicyId && !is_array($termsandpolicyId)) {
            $termsandpolicyId = array($termsandpolicyId);
        }
        if ($termsandpolicyId) {
            return self::whereIn('id', $termsandpolicyId)->where([
                ['account_id', '=', session('account_id')]
            ])->get()->pluck('name', 'description','id');
        } else {
            return self::where([
                ['account_id', '=', session('account_id')]
            ])->pluck('name', 'description','id');
        }
    }

    /**
     * Get active and sorted data only.
     */
    // static public function getActiveSortedFeatured($cityId = false, $name = 'name')
    // {
    //     if ($cityId && !is_array($cityId)) {
    //         $cityId = array($cityId);
    //     }

    //     $query = self::where(['active' => 1, 'is_featured' => 1]);
    //     if ($cityId) {
    //         $query->whereIn('id', $cityId);
    //     }
    //     return $query->get()->pluck($name, 'id');
    // }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveOnly($termsandpolicyId = false, $account_id = false)
    {
        if ($termsandpolicyId && !is_array($termsandpolicyId)) {
            $termsandpolicyId = array($termsandpolicyId);
        }
        $query = self::where(['active' => 1]);
        if ($termsandpolicyId) {
            $query->whereIn('id', $termsandpolicyId);
        }
        if ($account_id) {
            $query->where([
                ['account_id', '=', $account_id]
            ]);
        }
        return $query->OrderBy('id', 'desc')->get();
    }

    /**
     * Get the Location name with City Name.
     */
    public function getFullNameAttribute($value)
    {
        return ucfirst($this->region->name) . ' - ' . ucfirst($this->name);
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveFeaturedOnly($termsandpolicyId = false, $account_id)
    {
        if ($termsandpolicyId && !is_array($termsandpolicyId)) {
            $termsandpolicyId = array($termsandpolicyId);
        }

        $query = self::where(['active' => 1, 'account_id' => $account_id]);
        if ($termsandpolicyId) {
            $query->whereIn('id', $termsandpolicyId);
        }
        return $query->OrderBy('id', 'desc');
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = Self::termsandpolicies_filters($request, $account_id, $apply_filter);
         // dd(self::count());
        if (count($where)) {
            // return self::where($where)->count();
            return self::count();
        } else {
            return self::count();
        }
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
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $apply_filter = false)
    {
        $where = Self::termsandpolicies_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('id')->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy('id')->get();
        }
    }

    /**
     * Get filters
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     * @param (boolean) $apply_filter
     * @return (mixed)
     */
    static public function termsandpolicies_filters($request, $account_id, $apply_filter)
    {

        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'termsandpolicies', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'termsandpolicies', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'termsandpolicies', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'termsandpolicies', 'account_id')
                    );
                }
            }
        }
        if ($request->get('name')) {
            $where[] = array(
                'name',
                'like',
                '%' . $request->get('name') . '%'
            );
            Filters::put(Auth::User()->id, 'termsandpolicies', 'name', $request->get('name'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'termsandpolicies', 'name');
            } else {
                if (Filters::get(Auth::User()->id, 'termsandpolicies', 'name')) {
                    $where[] = array(
                        'name',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'termsandpolicies', 'name') . '%'
                    );
                }
            }
        }
        if ($request->get('description')) {
            $where[] = array(
                'description',
                'like',
                '%' . $request->get('description') . '%'
            );
            Filters::put(Auth::User()->id, 'termsandpolicies', 'description', $request->get('description'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'termsandpolicies', 'description');
            } else {
                if (Filters::get(Auth::User()->id, 'termsandpolicies', 'description')) {
                    $where[] = array(
                        'description',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'termsandpolicies', 'description') . '%'
                    );
                }
            }
        }
        
        if ( $request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null ){
            $where[] = array(
                'active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, 'termsandpolicies', 'status', $request->get('status'));
        } else {
            if ( $apply_filter ){
                Filters::forget( Auth::user()->id, 'termsandpolicies', 'status');
            } else {
                if ( Filters::get(Auth::user()->id, 'termsandpolicies', 'status' ) == 0 || Filters::get(Auth::user()->id, 'termsandpolicies', 'status' ) == 1 ){
                    if ( Filters::get(Auth::user()->id, 'termsandpolicies', 'status' ) != null ){
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get( Auth::user()->id, 'termsandpolicies', 'status')
                        );
                    }
                }
            }
        }

        //dd($where);
        return $where;
    }


    /**
     * Get All Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllRecordsDictionary($account_id, $termsandpoliciesids = false)
    {
        if ($termsandpoliciesids && !is_array($termsandpoliciesids)) {
            $termsandpoliciesids = array($termsandpoliciesids);
        }
        if ($termsandpoliciesids) {
            return self::where(['account_id' => $account_id])->whereIn('id', $termsandpoliciesids)->get()->getDictionary();
        } else {
            return self::where(['account_id' => $account_id])->get()->getDictionary();
        }
    }

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request, $account_id)
    {

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;

        // if (!isset($data['is_featured'])) {
        //     $data['is_featured'] = 0;
        // } else if ($data['is_featured'] == '') {
        //     $data['is_featured'] = 0;
        // }

        $record = self::create($data);

        // $record->update(['sort_no' => $record->id]);

        //log request for Create for Audit Trail

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return $record;
    }

    /**
     * Delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function DeleteRecord($id)
    {
        
        $termsandpolicy = TermsAndPolicies::getData($id);

        if (!$termsandpolicy) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.termsandpolicies.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        // if (TermsAndPolicies::isChildExists($id, Auth::User()->account_id)) {

            // flash('Child records exist, unable to delete resource')->error()->important();
            // return redirect()->route('admin.termsandpolicies.index');
        // }

        $record = $termsandpolicy->delete();

        //log request for delete for audit trail

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;

    }

    /**
     * inactive Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id)
    {

        $termsandpolicy = TermsAndPolicies::getData($id);

        if (!$termsandpolicy) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.termsandpolicies.index');
        }

        $record = $termsandpolicy->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static function activeRecord($id)
    {

        $termsandpolicy = TermsAndPolicies::getData($id);

        if (!$termsandpolicy) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.termsandpolicies.index');
        }

        $record = $termsandpolicy->update(['active' => 1]);

        flash('Record has been activated successfully.')->success()->important();

        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

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
        $old_data = (TermsAndPolicies::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;

        // if (!isset($data['is_featured'])) {
        //     $data['is_featured'] = 0;
        // } else if ($data['is_featured'] == '') {
        //     $data['is_featured'] = 0;
        // }


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
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    // static public function isChildExists($id, $account_id)
    // {
    //     if (
    //         Locations::where(['city_id' => $id, 'account_id' => $account_id])->count() ||
    //         Leads::where(['city_id' => $id, 'account_id' => $account_id])->count() ||
    //         Appointments::where(['city_id' => $id, 'account_id' => $account_id])->count()
    //     ) {
    //         return true;
    //     }

    //     return false;
    // }

    static public function getTermsAndPolicies(){
    

        return self::where([
            ['account_id', '=', session('account_id')],
            ['active', '=', '1'],
        ])->OrderBy('id', 'desc')->get()->pluck('name', 'description', 'id');

    }
}
