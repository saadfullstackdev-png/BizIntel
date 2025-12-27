@inject('request', 'Illuminate\Http\Request')
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
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"
          rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-editable/inputs-ext/address/address.css') }}"
          rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->

    <link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        #service_id span.select2-container {
            z-index: 10050;
        }
    </style>
@stop
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.reports.hr_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-body sn-panel">
            <div class="box box-primary">
                <div class="panel-body pad table-responsive">
                    <div class="row">
                        <div class="form-group col-md-2 sn-select @if($errors->has('date_range')) has-error @endif">
                            {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif">
                            {!! Form::label('location_id', 'Centre*', ['class' => 'control-label']) !!}
                            <select name="location_id" id="location_id" style="width: 100%"
                                    class="form-control select2">
                                <option value="">Select a Centre</option>
                                @foreach($locations as $location)
                                    <option value="{{$location->id}}">{{$location->city->name}}
                                        -{{$location->name}}</option>
                                @endforeach
                            </select>
                            <span id="location_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                            {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                            <select name="report_type" id="report_type" style="width: 100%;"
                                    class="form-control select2">
                                @if(Gate::allows('Hr_reports_reports_for_calculating_incentives'))
                                    <option value="default">Select a Report Type</option>
                                @endif
                                @if(Gate::allows('Hr_reports_reports_for_calculating_incentives'))
                                    <option value="reports_for_calculating_incentives">Reports For Calculating
                                        Incentives
                                    </option>
                                @endif
                                @if(Gate::allows('Hr_reports_reports_for_calculating_incentives_detail'))
                                    <option value="reports_for_calculating_incentives_detail">Reports For Calculating
                                        Incentives Detail
                                    </option>
                                @endif
                                @if(Gate::allows('Hr_reports_revenue_generated_by_operators_application_user'))
                                    <option value="revenue_generated_by_operators_application_user">Revenue Generated By
                                        Operators (Application User)
                                    </option>
                                @endif
                                @if(Gate::allows('Hr_reports_revenue_generated_by_consultants_practitioner'))
                                    <option value="revenue_generated_by_consultants_practitioner">Revenue Generated By
                                        Consultants (Practitioner)
                                    </option>
                                @endif
                            </select>
                            <span id="report_type_handler"></span>
                        </div>

                        <div style="display: none;"
                             class="form-group col-md-2 sn-select @if($errors->has('role_id')) has-error @endif" id="roles">
                            {!! Form::label('role_id', 'Role*', ['class' => 'control-label']) !!}
                            {!! Form::select('role_id', $roles, null, ['id' => 'role_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="role_id_handler"></span>
                        </div>
                        <div style="display: none;"
                             class="form-group col-md-2 sn-select @if($errors->has('search_type')) has-error @endif"
                             id="searchtypes">
                            {!! Form::label('search_type', 'Search Type*', ['class' => 'control-label']) !!}
                            <select name="search_type" id="search_type" style="width: 100%"
                                    class="form-control select2">
                                <option value="">Select A Type</option>
                                <option value="created_by">Created By</option>
                                <option value="updated_by">Updated By</option>
                                <option value="converted_by">Convereted By</option>
                                <option value="doctor_id">Performed By (Doctor)</option>

                            </select>
                            <span id="search_type_handler"></span>
                        </div>
                        <div style="display: none;"
                             class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif" id="users">
                            {!! Form::label('Create_id', 'Users', ['class' => 'control-label']) !!}
                            {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div style="display: none;"
                             class="form-group col-md-2 sn-select @if($errors->has('application_user_id')) has-error @endif"
                             id="applicationuser">
                            {!! Form::label('Create_id', 'Application User', ['class' => 'control-label']) !!}
                            {!! Form::select('application_user_id', $applicationusers, null, ['id' => 'application_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="application_user_id_handler"></span>
                        </div>
                        <div style="display: none;"
                             class="form-group col-md-2 sn-select @if($errors->has('practitioner_id')) has-error @endif"
                             id="practitioner">
                            {!! Form::label('Create_id', 'Practitioner', ['class' => 'control-label']) !!}
                            {!! Form::select('practitioner_id', $practitionor, null, ['id' => 'practitioner_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="practitioner_id_handler"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                            {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                            <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                               class="btn btn-success">Load Report</a>
                        </div>
                        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                    </div>
                    <div class="clear clearfix"></div>
                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.HR_report_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                    {!! Form::hidden('role_id', null, ['id' => 'role_id-report']) !!}
                    {!! Form::hidden('search_type', null, ['id' => 'search_type-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                    {!! Form::hidden('application_user_id', null, ['id' => 'application_user_id-report']) !!}
                    {!! Form::hidden('practitioner_id', null, ['id' => 'practitioner_id-report']) !!}
                    {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
    <script>
        $(document).on('change', '#report_type', function () {
            var type_p = $("#report_type").val();
            if (type_p == 'reports_for_calculating_incentives' || type_p == 'reports_for_calculating_incentives_detail') {
                $("#searchtypes").show();
                $("#roles").show();
                $("#users").show();
                $("#practitioner").hide();
                $("#applicationuser").hide();
            } else if (type_p == 'revenue_generated_by_operators_application_user') {
                $("#searchtypes").show();
                $("#roles").hide();
                $("#users").hide();
                $("#practitioner").hide();
                $("#applicationuser").show();
            } else if (type_p == 'revenue_generated_by_consultants_practitioner') {
                $("#searchtypes").show();
                $("#roles").hide();
                $("#users").hide();
                $("#practitioner").show();
                $("#applicationuser").hide();
            } else {
                $("#searchtypes").show();
                $("#roles").show();
                $("#users").show();
                $("#practitioner").hide();
                $("#applicationuser").hide();
            }
        });
        $('#report_type').change();
    </script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-inputmask/jquery.inputmask.bundle.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-ui/jquery-ui.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/clipboard/clipboard.min.js') }}"></script>
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-editable/bootstrap-editable/js/bootstrap-editable.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-editable/inputs-ext/address/address.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/scripts/app.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/pages/scripts/components-date-time-pickers.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/reports/hrreport/general.js') }}" type="text/javascript"></script>
@endsection