<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;
use App\Helpers\ACL;
use App\Helpers\Filters;

class Faqs extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['question', 'answer', 'category_id', 'created_at', 'updated_at', 'deleted_at', 'active', 'account_id'];

    protected static $_fillable = ['question', 'answer', 'category_id'];

    protected $table = 'faqs';

    protected static $_table = 'faqs';

    /**
     * Get the category for FAQ.
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveSorted($faqId = false)
    {
        if ($faqId && !is_array($faqId)) {
            $faqId = array($faqId);
        }
        if ($faqId) {
            return self::whereIn('id', $faqId)->where([
                ['account_id', '=', session('account_id')]
            ])->get()->pluck('question', 'answer', 'id');
        } else {
            return self::where([
                ['account_id', '=', session('account_id')]
            ])->pluck('question', 'answer', 'id');
        }
    }

    /**
     * Get active and sorted data only.
     */
    static public function getActiveOnly($faqId = false, $account_id = false)
    {
        if ($faqId && !is_array($faqId)) {
            $faqId = array($faqId);
        }
        $query = self::where(['active' => 1]);
        if ($faqId) {
            $query->whereIn('id', $faqId);
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
    static public function getActiveFeaturedOnly($faqId = false, $account_id)
    {
        if ($faqId && !is_array($faqId)) {
            $faqId = array($faqId);
        }

        $query = self::where(['active' => 1, 'account_id' => $account_id]);
        if ($faqId) {
            $query->whereIn('id', $faqId);
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
        $where = Self::faqs_filters($request, $account_id, $apply_filter);
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
        $where = Self::faqs_filters($request, $account_id, $apply_filter);

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
    static public function faqs_filters($request, $account_id, $apply_filter)
    {

        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'faqs', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'faqs', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'faqs', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'faqs', 'account_id')
                    );
                }
            }
        }
        if ($request->get('category_id') != '') {
            $where[] = array(
                'category_id',
                '=',
                $request->get('category_id')
            );
            Filters::put(Auth::User()->id, 'faqs', 'category_id', $request->get('category_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'faqs', 'category_id');
            } else {
                if (Filters::get(Auth::User()->id, 'faqs', 'category_id')) {
                    $where[] = array(
                        'category_id',
                        '=',
                        Filters::get(Auth::User()->id, 'faqs', 'category_id')
                    );
                }
            }
        }
        if ($request->get('answer')) {
            $where[] = array(
                'answer',
                'like',
                '%' . $request->get('answer') . '%'
            );
            Filters::put(Auth::User()->id, 'faqs', 'answer', $request->get('answer'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'faqs', 'answer');
            } else {
                if (Filters::get(Auth::User()->id, 'faqs', 'answer')) {
                    $where[] = array(
                        'answer',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'faqs', 'answer') . '%'
                    );
                }
            }
        }

        if ($request->get('question')) {
            $where[] = array(
                'question',
                'like',
                '%' . $request->get('question') . '%'
            );
            Filters::put(Auth::User()->id, 'faqs', 'question', $request->get('question'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'faqs', 'question');
            } else {
                if (Filters::get(Auth::User()->id, 'faqs', 'question')) {
                    $where[] = array(
                        'question',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'faqs', 'question') . '%'
                    );
                }
            }
        }

        if ($request->get('status') && $request->get('status') != null || $request->get('status') == 0 && $request->get('status') != null) {
            $where[] = array(
                'active',
                '=',
                $request->get('status')
            );
            Filters::put(Auth::user()->id, 'faqs', 'status', $request->get('status'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::user()->id, 'faqs', 'status');
            } else {
                if (Filters::get(Auth::user()->id, 'faqs', 'status') == 0 || Filters::get(Auth::user()->id, 'faqs', 'status') == 1) {
                    if (Filters::get(Auth::user()->id, 'faqs', 'status') != null) {
                        $where[] = array(
                            'active',
                            '=',
                            Filters::get(Auth::user()->id, 'faqs', 'status')
                        );
                    }
                }
            }
        }

        // dd($where);
        return $where;
    }


    /**
     * Get All Records
     *
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getAllRecordsDictionary($account_id, $faqsids = false)
    {
        if ($faqsids && !is_array($faqsids)) {
            $faqsids = array($faqsids);
        }
        if ($faqsids) {
            return self::where(['account_id' => $account_id])->whereIn('id', $faqsids)->get()->getDictionary();
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

        $record = self::create($data);

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
        $faq = Faqs::getData($id);

        if (!$faq) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.faqs.index');
        }

        $record = $faq->delete();

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

        $faq = Faqs::getData($id);

        if (!$faq) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.faqs.index');
        }

        $record = $faq->update(['active' => 0]);

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

        $faq = Faqs::getData($id);

        if (!$faq) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.faqs.index');
        }

        $record = $faq->update(['active' => 1]);

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
        $old_data = (Faqs::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;

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

    static public function getFaqs()
    {
        return self::where([
            ['account_id', '=', session('account_id')],
            ['active', '=', '1'],
        ])->OrderBy('id', 'desc')->get()->pluck('question', 'answer', 'id');

    }
}
