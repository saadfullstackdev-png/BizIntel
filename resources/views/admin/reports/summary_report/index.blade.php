@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
<!-- BEGIN PAGE LEVEL PLUGINS -->
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"
    rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL PLUGINS -->

<link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
    rel="stylesheet" type="text/css" />

<link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css" />

<style type="text/css">
    #service_id span.select2-container {
        z-index: 10050;
    }
</style>
@stop
@section('title')
<!-- BEGIN PAGE TITLE-->
<h1 class="page-title">Summary Reports</h1>
<!-- END PAGE TITLE-->
@endsection
@section('content')
<!-- Begin: Demo Datatable 1 -->
<div class="portlet light portlet-fit portlet-datatable bordered">
    <div class="portlet-body sn-panel">
        <div class="form-group col-sm-3 sn-select  @if($errors->has('date_range')) has-error @endif">
            {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
            <div class="input-group">
                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                <div class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </div>
            </div>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('doctor_id')) has-error @endif"
            id="date_range_by_E">
            {!! Form::label('date_range_by', 'Date Filter By', ['class' => 'control-label']) !!}
            {!! Form::select('date_range_by', ['created_at' => 'Created At', 'scheduled_date' => 'Scheduled At'], null, ['id' => 'date_range_by', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="doctor_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('patient_id')) has-error @endif"
            id="patient_id_E">
            {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
            <select name="patient_id" id="patient_id" class="form-control patient_id" style="width: 100%;"></select>
            <span id="patient_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('doctor_id')) has-error @endif" id="doctor_id_E">
            {!! Form::label('doctor_id', 'Doctors', ['class' => 'control-label']) !!}
            {!! Form::select('doctor_id', $doctors, null, ['id' => 'doctor_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="doctor_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('region_id')) has-error @endif" id="region_id_E">
            {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
            {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="region_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select" id="city_id_E">
            {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
            {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
        </div>
        <div class="form-group col-sm-3">
            {!! Form::label('location_id', 'Location', ['class' => 'control-label']) !!}
            {!! Form::select('location_id[]', $locations->pluck('name', 'id'), null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'location_id']) !!}
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('service_id')) has-error @endif"
            id="service_id_E">
            {!! Form::label('service_id', 'Services', ['class' => 'control-label']) !!}
            <select class="form-control select2" id="service_id" name="service_id" style="width: 100%;">
                <option value="">Select Service</option>
                @foreach($services as $id => $service)
                @if ($id == 0) @continue; @endif
                @if($id < 0)
                    @php($tmp_id=($id * -1))
                    @else
                    @php($tmp_id=($id * 1))
                    @endif
                    <option value="@if($id < 0){{ ($id * -1) }}@else{{ $id }}@endif">@if($id < 0)
                        <b>{!! $service['name'] !!}</b>@else{!! $service['name'] !!}@endif</option>
                        @endforeach
            </select>
            <span id="service_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('appointment_status_id')) has-error @endif"
            id="appointment_status_id_E">
            {!! Form::label('appointment_status_id', 'Appointment Status', ['class' => 'control-label']) !!}
            {!! Form::select('appointment_status_id', $appointment_statuses, null, ['id' => 'appointment_status_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="appointment_status_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('appointment_type_id')) has-error @endif"
            id="appointment_type_id_E">
            {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
            {!! Form::select('appointment_type_id', $appointment_types, null, ['id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="appointment_type_id_handler"></span>
        </div>
        <div class="form-group col-sm-3 sn-select @if($errors->has('consultancy_type')) has-error @endif"
            id="consultancy_type_E">
            {!! Form::label('consultancy_type', 'Consultancy Type', ['class' => 'control-label']) !!}
            {!! Form::select('consultancy_type', array('' => 'All') + Config::get("constants.consultancy_type_array"), null, ['id' => 'consultancy_type', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="appointment_type_id_handler"></span>
        </div>
        <div class="form-group col-md-3 sn-select @if($errors->has('user_id')) has-error @endif" id="user_id_E">
            {!! Form::label('user_id', 'Created By', ['class' => 'control-label']) !!}
            {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="user_id_handler"></span>
        </div>
        <div class="form-group col-md-3 sn-select @if($errors->has('up_user_id')) has-error @endif"
            id="up_user_id_E">
            {!! Form::label('up_user_id', 'Updated By', ['class' => 'control-label']) !!}
            {!! Form::select('up_user_id', $users, null, ['id' => 'up_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="up_user_id_handler"></span>
        </div>
        <div class="form-group col-md-3 sn-select @if($errors->has('re_user_id')) has-error @endif"
            id="re_user_id_E">
            {!! Form::label('re_user_id', 'Rescheduled By', ['class' => 'control-label']) !!}
            {!! Form::select('re_user_id', $users, null, ['id' => 're_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="re_user_id_handler"></span>
        </div>
        {{-- <div class="form-group col-sm-3 sn-select  @if($errors->has('appointment_type_id')) has-error @endif"
            id="referred_by_E">
            {!! Form::label('referred_by', 'Referred By', ['class' => 'control-label']) !!}
            {!! Form::select('referred_by', $users, null, ['id' => 'referred_by', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
            <span id="referred_by_handler"></span>
        </div> --}}
        <div class="form-group col-md-3 @if($errors->has('is_converted')) has-error @endif" id="is_converted_E">
            {!! Form::label('is_converted', 'Conversion Status', ['class' => 'control-label']) !!}
            {!! Form::select('is_converted', ['all' => 'All','converted' => 'Converted', 'not-converted' => 'Not Converted'], null, ['id' => 'is_converted', 'class' => 'form-control select2']) !!}
            <span id="is_converted_handler"></span>
        </div>
        <div class="form-group col-sm-3">
            {!! Form::label('lead_source_id', 'Lead Source', ['class' => 'control-label']) !!}
            {!! Form::select('lead_source_id[]', $lead_sources->pluck('name', 'id'), null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'lead_source_id']) !!}
        </div>
        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
        <div class="form-group col-sm-3 sn-select  @if($errors->has('group_id')) has-error @endif">
            {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br />
            <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                class="btn sn-btn btn-success">Load Report</a>
        </div>
        <div class="clear clearfix"></div>

        <div id="content"></div>

        {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.summary_report_load'], 'id' => 'report-form']) !!}
        {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
        {!! Form::hidden('date_range_by', null, ['id' => 'date_range_by-report']) !!}
        {!! Form::hidden('date_range_by_first', null, ['id' => 'date_range_by_first-report']) !!}
        {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
        {!! Form::hidden('scheduled_date', null, ['id' => 'scheduled_date-report']) !!}
        {!! Form::hidden('doctor_id', null, ['id' => 'doctor_id-report']) !!}
        {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
        {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
        {!! Form::hidden('location_id[]', null, ['id' => 'location_id-report']) !!}
        {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
        {!! Form::hidden('appointment_status_id', null, ['id' => 'appointment_status_id-report']) !!}
        {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
        {!! Form::hidden('consultancy_type', null, ['id' => 'consultancy_type-report']) !!}
        {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
        {!! Form::hidden('re_user_id', null, ['id' => 're_user_id-report']) !!}
        {!! Form::hidden('up_user_id', null, ['id' => 'up_user_id-report']) !!}
        {!! Form::hidden('referred_by', null, ['id' => 'referred_by-report']) !!}
        {!! Form::hidden('is_converted', null, ['id' => 'is_converted-report']) !!}
        {!! Form::hidden('lead_source_id[]', null, ['id' => 'lead_source_id-report']) !!}
        {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
        
        {!! Form::close() !!}
        

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
<script src="{{ url('js/admin/reports/summary/general.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection