@inject('request', 'Illuminate\Http\Request')
@inject('Auth', 'Auth')
@inject('filters', 'App\Helpers\Filters')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        .city span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.transactions.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.app_list')</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th>@lang('global.transactions.fields.user')</th>
                        <th>@lang('global.transactions.fields.Payment')</th>
                        <th>@lang('global.transactions.fields.Order')</th>
                        <th>@lang('global.transactions.fields.amount')</th>
                        <th>@lang('global.transactions.fields.paid_for')</th>
                        <th>@lang('global.transactions.fields.paid_for_id')</th>
                        <th>@lang('global.transactions.fields.status')</th>
                        <th>@lang('global.transactions.fields.attempt')</th>
                        <th>@lang('global.transactions.fields.message')</th>
                        <th>@lang('global.transactions.fields.created_at')</th>
                        <th width="20%">@lang('global.transactions.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <select name="patient_id" id="patient_id"
                                    class="form-control form-filter input-sm select2 patient_id">
                                @if(count($patient))
                                    <option value="{{ $patient['id'] }}">{{ $patient['name'] . ' - ' . $patient['phone'] }} </option>
                                @endif
                            </select>
                        </td>
                        <td>
                            {!! Form::select('payment_mode_id', $payments, $filters->get( Auth::user()->id , 'transactions', 'payment_mode_id'), ['class' => 'form-control form-filter input-sm select2']) !!}
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" value="{{ $filters->get(Auth::User()->id, 'transactions', 'order_id') }}" name="order_id">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" value="{{ $filters->get(Auth::User()->id, 'transactions', 'amount') }}" name="amount">
                        </td>
                        <td> {!! Form::select('paid_for', ['package' => 'Package', 'wallet' => 'Wallet', 'plan' => 'Plan'], $filters->get( Auth::user()->id , 'transactions', 'paid_for'), ['class' => 'form-control form-filter input-sm select2', 'placeholder' => 'All']) !!}</td>
                        <td></td>
                        <td>
                            {!! Form::select('status', ['pending' => 'Pending', 'cancelled' => 'Cancelled', 'success' => 'Success'], $filters->get( Auth::user()->id , 'transactions', 'status'), ['class' => 'form-control form-filter input-sm select2', 'placeholder' => 'All']) !!}
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" value="{{ $filters->get(Auth::User()->id, 'transactions', 'attempt') }}" name="attempt">
                        </td>
                        <td></td>
                        <td>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_from" data-date-format="yyyy-mm-dd" type="text" readonly=""
                                       value="{{ ($filters->get(Auth::User()->id, 'transactions', 'created_from')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'transactions', 'created_from'))->format('Y-m-d') : '' }}"
                                       class="form-control form-filter input-sm created_from" placeholder="From">
                            </div>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_to" data-date-format="yyyy-mm-dd" type="text" readonly=""
                                       value="{{ ($filters->get(Auth::User()->id, 'transactions', 'created_to')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'transactions', 'created_to'))->format('Y-m-d') : '' }}"
                                       class="form-control form-filter input-sm created_to" placeholder="To">
                            </div>
                        </td>
                        <td>
                            <div class="margin-bottom-5">
                                <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button class="btn btn-sm red btn-outline filter-cancel reset_custom">
                                    <i class="fa fa-times"></i> Reset
                                </button>
                            </div>
                        </td>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable 1 -->
    <!--Add New View model Start-->
    <div class="modal fade" id="ajax_cities" role="basic" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Add new View model End-->
@stop

@section('javascript')
    <script>
        $(".reset_custom").click(function () {
            $('.select2').val(null).trigger('change');
        });
    </script>
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/transactions/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
            type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>

@endsection