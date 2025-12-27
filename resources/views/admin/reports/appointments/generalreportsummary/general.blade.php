@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet"
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

    <link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>

    <style type="text/css">
        #service_id span.select2-container {
            z-index: 10050;
        }
    </style>
@stop
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.reports.appointment_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-body">
            <div class="box box-primary">
                <div class="panel-body pad table-responsive">
                    <div class="form-group col-md-2  @if($errors->has('date_range')) has-error @endif">
                        {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('doctor_id')) has-error @endif">
                        {!! Form::label('date_range_by', 'Date Filter By', ['class' => 'control-label']) !!}
                        {!! Form::select('date_range_by', ['created_at' => 'Created At', 'scheduled_date' => 'Scheduled At'], null, ['id' => 'date_range_by', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="doctor_id_handler"></span>
                    </div>
                    <div class="form-group col-md-4 @if($errors->has('patient_id')) has-error @endif">
                        {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                        <select name="patient_id" id="patient_id" class="form-control patient_id"></select>
                        <span id="patient_id_handler"></span>
                    </div>
                    {{--<div class="form-group col-md-2  @if($errors->has('scheduled_date')) has-error @endif">--}}
                    {{--{!! Form::label('scheduled_date', 'Scheduled Date*', ['class' => 'control-label']) !!}--}}
                    {{--<div class="input-group">--}}
                    {{--<div class="input-group-addon">--}}
                    {{--<i class="fa fa-calendar"></i>--}}
                    {{--</div>--}}
                    {{--{!! Form::text('scheduled_date', null, ['id' => 'scheduled_date', 'class' => 'form-control']) !!}--}}
                    {{--</div>--}}
                    {{--</div>--}}
                    <div class="form-group col-md-2 @if($errors->has('doctor_id')) has-error @endif">
                        {!! Form::label('doctor_id', 'Doctors', ['class' => 'control-label']) !!}
                        {!! Form::select('doctor_id', $doctors, null, ['id' => 'doctor_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="doctor_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('region_id')) has-error @endif">
                        {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
                        {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="region_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('city_id')) has-error @endif">
                        {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                        {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="city_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('location_id')) has-error @endif">
                        {!! Form::label('location_id', 'Centres', ['class' => 'control-label']) !!}
                        {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="location_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('service_id')) has-error @endif">
                        {!! Form::label('service_id', 'Services', ['class' => 'control-label']) !!}
                        <select class="form-control select2" id="service_id" name="service_id">
                            <option value="">Select Service</option>
                            @foreach($services as $id => $service)
                                @if ($id == 0) @continue; @endif
                                @if($id < 0)
                                    @php($tmp_id = ($id * -1))
                                @else
                                    @php($tmp_id = ($id * 1))
                                @endif
                                <option value="@if($id < 0){{ ($id * -1) }}@else{{ $id }}@endif">@if($id < 0)
                                        <b>{!! $service['name'] !!}</b>@else{!! $service['name'] !!}@endif</option>
                            @endforeach
                        </select>
                        <span id="service_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('appointment_status_id')) has-error @endif">
                        {!! Form::label('appointment_status_id', 'Appointment Status', ['class' => 'control-label']) !!}
                        {!! Form::select('appointment_status_id', $appointment_statuses, null, ['id' => 'appointment_status_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="appointment_status_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('appointment_type_id')) has-error @endif">
                        {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
                        {!! Form::select('appointment_type_id', $appointment_types, null, ['id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="appointment_type_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('user_id')) has-error @endif">
                        {!! Form::label('user_id', 'Created By', ['class' => 'control-label']) !!}
                        {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="user_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('up_user_id')) has-error @endif">
                        {!! Form::label('up_user_id', 'Updated By', ['class' => 'control-label']) !!}
                        {!! Form::select('up_user_id', $users, null, ['id' => 'up_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="up_user_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('re_user_id')) has-error @endif">
                        {!! Form::label('re_user_id', 'Rescheduled By', ['class' => 'control-label']) !!}
                        {!! Form::select('re_user_id', $users, null, ['id' => 're_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="re_user_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('appointment_type_id')) has-error @endif">
                        {!! Form::label('referred_by', 'Referred By', ['class' => 'control-label']) !!}
                        {!! Form::select('referred_by', $users, null, ['id' => 'referred_by', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="referred_by_handler"></span>
                    </div>
                    {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                    <div class="form-group col-md-2 @if($errors->has('report_type')) has-error @endif">
                        {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                        <select name="report_type" id="report_type" style="width: 100%" class="form-control select2">
                            @if(Gate::allows('appointment_reports_general_report'))
                                <option value="default">Select a Report Type</option>
                            @endif
                            @if(Gate::allows('appointment_reports_general_report'))
                                <option value="general">General Report</option>
                            @endif
                            @if(Gate::allows('appointment_reports_general_summary_report'))
                                <option value="general_summary">General Report Summary</option>
                            @endif
                            @if(Gate::allows('appointment_reports_staff_appointment'))
                                <option value="staff_appointment">Staff Wise Appointment Report</option>
                            @endif
                            @if(Gate::allows('appointment_reports_referred_by_staff_appointment'))
                                <option value="referred_by_staff_appointment">Staff Wise (Referred By) Appointment
                                    Report
                                </option>
                            @endif
                            @if(Gate::allows('appointment_reports_empolyee_summary'))
                                <option value="empolyee_summary">Appointment Summary Report</option>
                            @endif
                            @if(Gate::allows('appointment_reports_summary_by_service'))
                                <option value="summary_by_service">Appointments Summary by Service</option>
                            @endif
                            @if(Gate::allows('appointment_reports_summary_by_appointment_status'))
                                <option value="summary_by_appointment_status">Appointments Summary by Status</option>
                            @endif
                            @if(Gate::allows('appointment_reports_clients_by_appointment_status'))
                                <option value="clients_by_appointment_status">Patient by Appointment Status (Date
                                    Wise)
                                </option>
                            @endif
                        </select>
                        <span id="report_type_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('group_id')) has-error @endif">
                        {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                        <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                           class="btn btn-success">Load Report</a>
                    </div>
                    <div class="clear clearfix"></div>

                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.appointments_general_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('date_range_by', null, ['id' => 'date_range_by-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('scheduled_date', null, ['id' => 'scheduled_date-report']) !!}
                    {!! Form::hidden('doctor_id', null, ['id' => 'doctor_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('appointment_status_id', null, ['id' => 'appointment_status_id-report']) !!}
                    {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                    {!! Form::hidden('re_user_id', null, ['id' => 're_user_id-report']) !!}
                    {!! Form::hidden('up_user_id', null, ['id' => 'up_user_id-report']) !!}
                    {!! Form::hidden('referred_by', null, ['id' => 'referred_by-report']) !!}
                    {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                    {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
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
    <script src="{{ url('js/admin/reports/appointments/general.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>

@endsection