@inject('request', 'Illuminate\Http\Request')
@inject('Auth', 'Auth')
@inject('filters', 'App\Helpers\Filters')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>

    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />


    <link href="{{ url('metronic/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/components.min.css') }}" rel="stylesheet" id="style_components" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/plugins.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css') }}" rel="stylesheet" type="text/css"/>

    <link href="{{ url('metronic/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css') }}" rel="stylesheet" type="text/css" />

    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        #service span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.locations.title')</h1>
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
                @if(Gate::allows('locations_create'))
                    <a class="btn btn-success" href="{{ route('admin.locations.create') }}" data-target="#ajax_locatons" data-toggle="modal">@lang('global.app_add_new')</a>
                @endif
                @if(Gate::allows('locations_sort'))
                    <a href="{{ route('admin.locations.sort') }}" class="btn btn-success">@lang('global.app_SortCenters')</a>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                @if(Gate::allows('locations_destroy'))
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
                        <th width="20%">@lang('global.locations.fields.name')</th>
                        <th>@lang('global.locations.fields.fdo_name')</th>
                        <th>@lang('global.locations.fields.fdo_phone')</th>
                        <th width="20%">@lang('global.locations.fields.address')</th>
                        <th>@lang('global.locations.fields.city')</th>
                        <th>@lang('global.locations.fields.region')</th>
                        <th>@lang('global.locations.fields.created_at')</th>
                        <th> @lang('global.locations.fields.status')</th>
                        <th width="13%">@lang('global.locations.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="lead_status_name" value="{{ $filters->get(Auth::User()->id, 'locations', 'lead_status_name') }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="lead_status_fdo_name" value="{{ $filters->get(Auth::User()->id, 'locations', 'lead_status_fdo_name') }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="lead_status_fdo_phone" value="{{ $filters->get(Auth::User()->id, 'locations', 'lead_status_fdo_phone') }}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-filter input-sm" name="lead_status_address" value="{{ $filters->get(Auth::User()->id, 'locations', 'lead_status_address') }}">
                        </td>
                        <td>
                            {!! Form::select('lead_status_city', $cities, $filters->get(Auth::User()->id, 'locations', 'lead_status_city'), ['class' => 'form-control form-filter input-sm select2']) !!}
                        </td>
                        <td>
                            {!! Form::select('region', $regions, $filters->get(Auth::User()->id, 'locations', 'region'), ['class' => 'form-control form-filter input-sm select2']) !!}
                        </td>
                        <td>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_from" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm created_from" placeholder="From" value="{{ ($filters->get(Auth::User()->id, 'locations', 'created_from')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'locations', 'created_from'))->format('Y-m-d') : '' }}">
                            </div>
                            <div class="input-icon input-icon-sm right margin-bottom-5">
                                <i class="fa fa-calendar"></i>
                                <input name="created_to" data-date-format="yyyy-mm-dd" type="text" readonly="" class="form-control form-filter input-sm created_to" placeholder="To" value="{{ ($filters->get(Auth::User()->id, 'locations', 'created_to')) ? Carbon\Carbon::parse($filters->get(Auth::User()->id, 'locations', 'created_to'))->format('Y-m-d') : '' }}">
                            </div>
                        </td>
                        <td> {!! Form::select('status', ['0' => 'Inactive', '1' => 'Active'], $filters->get( Auth::user()->id, 'locations', 'status') , ['class' => 'form-control form-filter input-sm select2', 'placeholder' => 'All']) !!}</td>
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
    <!--add new View model Start-->
    <div class="modal fade" id="ajax_locatons" role="basic" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--add new View model End-->
@stop

@section('javascript')
    <script>
        $( ".reset_custom" ).click(function() {
            $('.select2').val(null).trigger('change');
        });
    </script>
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/locations/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/component-multiselect.js') }}" type="text/javascript"></script>

    <script src="{{ url('metronic/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') }}" type="text/javascript"></script>
@endsection