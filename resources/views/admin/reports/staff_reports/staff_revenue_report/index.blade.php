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
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        #service_id span.select2-container {
            z-index: 10050;
        }
    </style>
@stop
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.reports.staff_revenue_report')</h1>
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
                        <div class="form-group col-md-4 sn-select @if($errors->has('patient_id')) has-error @endif">
                            {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                            <select name="patient_id" id="patient_id" class="form-control patient_id"></select>
                            <span id="patient_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('appointment_type_id')) has-error @endif">
                            {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
                            {!! Form::select('appointment_type_id', $appointment_types, null, ['id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="appointment_type_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif">
                            {!! Form::label('location_id', 'Centre', ['class' => 'control-label']) !!}
                            {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="location_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('service_id')) has-error @endif">
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
                                            <b>{!! $service['name'] !!}</b>@else{!! $service['name'] !!}@endif
                                    </option>
                                @endforeach
                            </select>
                            <span id="service_id_handler"></span>
                        </div>
                    </div>
                    <div class="row">
                        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                        <div class="form-group col-md-2 sn-select @if($errors->has('appointment_type_id')) has-error @endif">
                            {!! Form::label('user_id', 'Employee', ['class' => 'control-label']) !!}
                            {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                            {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                            <select name="report_type" id="report_type" style="width: 100%"
                                    class="form-control select2">
                                @if(Gate::allows('staff_revenue_reports_center_performance_stats_by_revenue'))
                                    <option value="default">Select a Report Type</option>
                                @endif
                                @if(Gate::allows('staff_revenue_reports_center_performance_stats_by_revenue'))
                                    <option value="center_performance_stats_by_revenue">Staff Revenue Centre Wise
                                    </option>
                                @endif
                                @if(Gate::allows('staff_revenue_reports_center_performance_stats_by_service_type'))
                                    <option value="center_performance_stats_by_service_type">Staff Revenue by Service
                                        Type
                                    </option>
                                @endif

                            </select>
                            <span id="report_type_handler"></span>
                        </div>
                            <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                                {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                                <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                                   class="btn btn-success">Load Report</a>
                            </div>
                    </div>

                    <div class="clear clearfix"></div>

                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.staff.revenue.report.load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
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
    <script src="{{ url('js/admin/reports/staff/staff_revenue.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection