<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Helpers\ACL;
use App\Helpers\Filters;
use Illuminate\Http\Request;

class Feedback extends BaseModal
{
    // use SoftDeletes;
    protected $fillable = ['user_id', 'subject', 'message', 'type', 'created_at', 'updated_at', 'account_id'];

    protected static $_fillable = ['user_id','email' ,'phone', 'subject', 'message','type','created_at'];

    protected $table = 'feedback';

    protected static $_table = 'feedback';

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request)
    {
        $record = self::create($request);

        return $record;
    }

    //New Code start
    public function usernameget()
    {
        return $this->belongsTo('App\User', 'user_id');
    }


    static public function DeleteRecord($id)
    {
        $feedback = Feedback::getData($id);
        if (!$feedback) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.feedbacks.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (Feedback::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.banner.index');
        }
        $record = $feedback->delete();

        //log request for delete for audit trail
        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

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
    static public function getTotalRecords(Request $request, $account_id = false, $apply_filter = false)
    {
        $where = Self::feedbacks_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->count();
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
        $where = Self::feedbacks_filters($request, $account_id, $apply_filter);

        if (count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy('created_at')->get();
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
    static public function feedbacks_filters($request, $account_id, $apply_filter)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'account_id', $account_id);
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'account_id');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'account_id')) {
                    $where[] = array(
                        'account_id',
                        '=',
                        Filters::get(Auth::User()->id, 'feedbacks', 'account_id')
                    );
                }
            }
        }

        if ($request->get('user_id ')) {
            $where[] = array(
                'user_id ',
                'like',
                '%' . $request->get('user_id') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'user_id ', $request->get('user_id'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'user_id');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'user_id')) {
                    $where[] = array(
                        'user_id',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'user_id') . '%'
                    );
                }
            }
        }

        if ($request->get('email')) {
            $where[] = array(
                'email',
                'like',
                '%' . $request->get('email') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'email', $request->get('email'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'email');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'email')) {
                    $where[] = array(
                        'email',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'email') . '%'
                    );
                }
            }
        }
        if ($request->get('phone')) {
            $where[] = array(
                'phone',
                'like',
                '%' . $request->get('phone') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'phone', $request->get('phone'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'phone');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'phone')) {
                    $where[] = array(
                        'phone',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'subject') . '%'
                    );
                }
            }
        }
        if ($request->get('subject')) {
            $where[] = array(
                'subject',
                'like',
                '%' . $request->get('subject') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'subject', $request->get('subject'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'subject');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'subject')) {
                    $where[] = array(
                        'subject',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'subject') . '%'
                    );
                }
            }
        }
        if ($request->get('message')) {
            $where[] = array(
                'message',
                'like',
                '%' . $request->get('message') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'message', $request->get('message'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'message');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'message')) {
                    $where[] = array(
                        'message',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'message') . '%'
                    );
                }
            }
        }
        if ($request->get('type')) {
            $where[] = array(
                'type',
                'like',
                '%' . $request->get('type') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'type', $request->get('type'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'type');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'type')) {
                    $where[] = array(
                        'type',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'type') . '%'
                    );
                }
            }
        }
        if ($request->get('created_at')) {
            $where[] = array(
                'created_at',
                'like',
                '%' . $request->get('created_at') . '%'
            );
            Filters::put(Auth::User()->id, 'feedbacks', 'type', $request->get('created_at'));
        } else {
            if ($apply_filter) {
                Filters::forget(Auth::User()->id, 'feedbacks', 'created_at');
            } else {
                if (Filters::get(Auth::User()->id, 'feedbacks', 'created_at')) {
                    $where[] = array(
                        'created_at',
                        'like',
                        '%' . Filters::get(Auth::User()->id, 'feedbacks', 'created_at') . '%'
                    );
                }
            }
        }
        return $where;
    }

    static public function isChildExists($id, $account_id)
    {
        return false;
    }
}
