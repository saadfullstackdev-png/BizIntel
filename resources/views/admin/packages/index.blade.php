@inject('request', 'Illuminate\Http\Request')
@inject('Auth', 'Auth')
@inject('filters', 'App\Helpers\Filters')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
          type="text/css"/>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.packages.title')</h1>
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
            <div class="actions">
                @if(Gate::allows('plans_create'))
                    <a class="btn btn-success" href="{{ route('admin.packages.create') }}">@lang('global.app_add_new')</a>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                @if(Gate::allows('plans_destroy'))
                    <div class="table-actions-wrapper">
                        <span> </span>
                        <select class="table-group-action-input form-control input-inline input-small input-sm">
                            <option value="">Select</option>
                            <option value="Delete">Delete</option>
                        </select>
                        <button class="btn btn-sm red table-group-action-submit">
                            <i class="fa fa-check"></i> Submit
                        </button>
                    </div>
                @endif
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th>@lang('global.packages.fields.patient_name')</th>
                        <th>@lang('global.packages.fields.package_id')</th>
                        <th>@lang('global.packages.fields.location')</th>
                        <th>@lang('global.packages.fields.session_count')</th>
                        <th>@lang('global.packages.fields.total')</th>
                        <th>@lang('global.packages.fields.cash_receive')</th>
                        <th>@lang('global.packages.fields.refund')</th>
                        <th>@lang('global.packages.fields.packageselling')</th>
                        <th>@lang('global.packages.fields.created_at')</th>
                        <th>@lang('global.packages.fields.status')</th>
                        <th width="20%">@lang('global.packages.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td width="20%">
                            <select name="patient_id" id="patient_id"
                                    class="form-control form-filter input-sm select2 patient_id">
                                @if(count($patient))
                                    <option value="{{ $patient['id'] }}">{{ $patient['name'] . ' - ' . $patient['phone'] }} </option>
                                @endif
                            </select>
                        </td>
                        <td width="10%">
                            <select name="package_id" id="package_id"
                                    class="form-control form-filter input-sm select2 package_id">
                                @if(count($package))
                                    <option value="{{ $package['id'] }}">{{ $package['name']}}</option>
                                @endif
                            </select>
                        </td>
                        <td>{!! Form::select('location_id', $locations, $filters->get(Auth::user()->id , 'packages' ,'location_id'), ['class' => 'form-control form-filter input-sm select2',]) !!}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <select name="package_selling_id" id="package_selling_id"
                                    class="form-control form-filter input-sm select2 package_selling_id">
                                @if(count($packageselling))
                                    <option value="{{ $packageselling['id'] }}">{{ $packageselling['id']}}</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_from" data-date-format="yyyy-mm-dd" type="text" readonly=""
                                       value="{{ ($filters->get(Auth::User()->id, 'packages', 'created_from')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'packages', 'created_from'))->format('Y-m-d') : '' }}"
                                       class="form-control form-filter input-sm created_from" placeholder="From">
                            </div>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_to" data-date-format="yyyy-mm-dd" type="text" readonly=""
                                       value="{{ ($filters->get(Auth::User()->id, 'packages', 'created_to')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'packages', 'created_to'))->format('Y-m-d') : '' }}"
                                       class="form-control form-filter input-sm created_to" placeholder="To">
                            </div>
                        </td>
                        <td> {!! Form::select('status', ['0' => 'Inactive', '1' => 'Active'] , $filters->get(Auth::user()->id, 'packages', 'status') , ['class' => 'form-control form-filter input-sm select2', 'placeholder' => 'All']) !!} </td>
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
    <!--Edit View model Start-->
    <div class="modal fade" id="ajax_packages" role="basic" aria-hidden="true">
        <div class="modal-content package-custom-popup">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Edit View model End-->
    <!-- End: plan sms log -->
    <div class="modal fade" id="plan_sms_logs" role="basic" aria-hidden="true" style=" left:10%; width: 80%; top:2%;">
        <div class="modal-content">
            <div class="modal-body">
                <img src="{{ url('metronic/assets/global/img/loading-spinner-grey.gif') }}" alt="" class="loading">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
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
    <script src="{{ url('js/admin/packages/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/packages/ajaxselect2_plan_id.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/packages/ajaxselect2_package_selling_id.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
@endsection