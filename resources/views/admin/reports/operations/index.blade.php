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
    <h1 class="page-title">@lang('global.reports.operations_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-body sn-panel">
            <div class="box box-primary">
                <div class="panel-body pad table-responsive">
                    <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                        {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                        <select name="report_type" id="report_type" onchange="LoadDaysArray()" style="width: 100%;"
                                class="form-control select2">
                            @if(Gate::allows('operations_reports_operations_company_health'))
                                <option value="default">Select a Report Type</option>
                            @endif
                            @if(Gate::allows('operations_reports_complimentory_report'))
                                <option value="complimentory_report">Complimentory Treatment Report</option>
                            @endif
                            @if(Gate::allows('operations_reports_operations_company_health'))
                                <option value="operations_company_health">Company Health Report</option>
                            @endif
                            @if(Gate::allows('operations_reports_center_target_report'))
                                <option value="center_target_report">Center Target Report</option>
                            @endif
                            @if(Gate::allows('operations_reports_Highest_paying_clients'))
                                <option value="Highest_paying_clients">Highest Paying Clients</option>
                            @endif
                            @if(Gate::allows('operations_reports_List_of_refunds_for_a_certain_period_date_based'))
                                <option value="List_of_refunds_for_a_certain_period_date_based">List of refunds for a
                                    certain period (date based)
                                </option>
                            @endif
                            @if(Gate::allows('operations_reports_List_of_services_that_CAN_be_offered_Complimentary'))
                                <option value="List_of_services_that_CAN_be_offered_Complimentary">List of services that
                                    CAN be offered Complimentary
                                </option>
                            @endif
                            @if(Gate::allows('operations_reports_List_of_services_that_CAN_not_be_offered_Complimentary'))
                                <option value="List_of_services_that_CAN_not_be_offered_Complimentary">List of services
                                    that CAN NOT be offered Complimentary
                                </option>
                            @endif
                            @if(Gate::allows('operations_reports_conversion_report_consultancy'))
                                <option value="conversion_report_consultancy">Conversion Report for Consultancy</option>
                            @endif
                            @if(Gate::allows('operations_reports_conversion_report_treatment'))
                                <option value="conversion_report_treatment">Conversion Report for treatment</option>
                            @endif
                            @if(Gate::allows('operations_reports_dar_report'))
                                <option value="dar_report">DAR Report</option>
                            @endif
                            @if(Gate::allows('operations_reports_dtr_report'))
                                <option value="dtr_report">DTR Report</option>
                            @endif
                        </select>
                        <span id="report_type_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('date_range')) has-error @endif"
                         id="date_range_e">
                        {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('month')) has-error @endif" id="month_e">
                        {!! Form::label('month', 'Month*', ['class' => 'control-label']) !!}
                        {!! Form::select('month', $months, \Carbon\Carbon::now()->format('m'), ['onchange' => 'LoadDaysArray();','id' => 'month', 'class' => 'form-control form-filter input-sm select2']) !!}
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('year')) has-error @endif" id="year_e">
                        {!! Form::label('year', 'Year*', ['class' => 'control-label']) !!}
                        {!! Form::select('year', $years, \Carbon\Carbon::now()->format('Y'), ['onchange' => 'LoadDaysArray();','id' => 'year', 'class' => 'form-control form-filter input-sm select2']) !!}
                    </div>
                    <div class="form-group col-sm-3 sn-select @if($errors->has('date_range_by')) has-error @endif"
                         id="date_range_by_E">
                        {!! Form::label('date_range_by', 'Date Filter By', ['class' => 'control-label']) !!}
                        {!! Form::select('date_range_by', ['created_at' => 'Created At', 'scheduled_date' => 'Scheduled At'], null, ['id' => 'date_range_by', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="date_range_by_handler"></span>
                    </div>
                    <div class="form-group col-sm-3 sn-select @if($errors->has('date_range_by_first')) has-error @endif"
                         id="date_range_by_L">
                        {!! Form::label('date_range_by_first', 'Date Filter By', ['class' => 'control-label']) !!}
                        {!! Form::select('date_range_by_first', ['created_at' => 'Created At', 'scheduled_date' => 'Scheduled At','first_scheduled_date' => 'First Scheduled At'], null, ['id' => 'date_range_by_first', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="date_range_by_first_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('day_number')) has-error @endif" id="day_number"
                         style="display: none;">
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('completed_working_days')) has-error @endif"
                         id="completed_working_days_E">
                        {!! Form::label('completed_working_days', 'Completed Working Days*', ['class' => 'control-label']) !!}
                        {!! Form::number('completed_working_days',0, ['id' => 'completed_working_days', 'class' => 'form-control', 'min' => '0']) !!}
                        <span id="completed_working_days_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('region_id')) has-error @endif"
                         id="regions">
                        {!! Form::label('region_id', 'Regions', ['class' => 'control-label']) !!}
                        {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="region_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('city_id')) has-error @endif"
                         id="city">
                        {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                        {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="city_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif"
                         id="locations">
                        {!! Form::label('location_id', 'Centre', ['class' => 'control-label']) !!}
                        {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="location_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('service_id')) has-error @endif"
                         id="services">
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
                    {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                    <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif"
                         id="users">
                        {!! Form::label('user_id', 'Practitioner', ['class' => 'control-label']) !!}
                        {!! Form::select('user_id', $employees, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="user_id_handler"></span>
                    </div>

                    <div class="form-group col-md-2 patients-cold sn-select @if($errors->has('patient_id')) has-error @endif"
                         id="patients">
                        {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                        <select name="patient_id" id="patient_id" class="form-control patient_id"></select>
                        <span id="patient_id_handler"></span>
                    </div>

                    <div style="display: none;" id="type_C"
                         class="form-group col-md-2 sn-select @if($errors->has('type')) has-error @endif">
                        {!! Form::label('type', 'Type*', ['class' => 'control-label']) !!}
                        {!! Form::select('type', array(''=>'Select Type','plan' => 'Plan', 'nonplan' => 'Non plan'), null, ['id' => 'type', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="report_type_handler"></span>
                    </div>
                    <div style="display: none;" id="appointment_type_C"
                         class="form-group col-md-2 sn-select @if($errors->has('appointment_type')) has-error @endif">
                        {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
                        {!! Form::select('appointment_type_id', $appointment_types, null, ['id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="appointment_type_id_handler"></span>
                    </div>

                    <div class="form-group col-sm-3 sn-select @if($errors->has('consultancy_type')) has-error @endif" id="consultancy_type_C">
                        {!! Form::label('consultancy_type', 'Consultancy Type', ['class' => 'control-label']) !!}
                        {!! Form::select('consultancy_type', array('' => 'All') + Config::get("constants.consultancy_type_array"), null, ['id' => 'consultancy_type', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="appointment_type_id_handler"></span>
                    </div>

                    <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                        {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                        <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                           class="btn btn-success">Load Report</a>
                    </div>
                    <div class="clear clearfix"></div>
                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.operations_report_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('date_range_by', null, ['id' => 'date_range_by-report']) !!}
                    {!! Form::hidden('date_range_by_first', null, ['id' => 'date_range_by_first-report']) !!}
                    {!! Form::hidden('month', null, ['id' => 'month-report']) !!}
                    {!! Form::hidden('year', null, ['id' => 'year-report']) !!}
                    {!! Form::hidden('days_count', null, ['id' => 'days_count-report']) !!}
                    {!! Form::hidden('completed_working_days', null, ['id' => 'completed_working_days-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                    {!! Form::hidden('type', null, ['id' => 'type-report']) !!}
                    {!! Form::hidden('consultancy_type', null, ['id' => 'consultancy_type-report']) !!}
                    {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                    {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
    <script src="{{ url('js/admin/reports/operations/loaddays.js') }}" type="text/javascript"></script>
    <script>
        $(document).on('change', '#report_type', function () {
            var type_p = $("#report_type").val();
            if (type_p == 'operations_company_health') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").show();
                // $("#day_number").hide();
                $("#regions").show();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').show();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'Highest_paying_clients') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").hide();
                $("#regions").show();
                $("#city").show();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'List_of_refunds_for_a_certain_period_date_based') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').show();
                $('#type_C').show();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'List_of_services_that_CAN_be_offered_Complimentary' || type_p == 'List_of_services_that_CAN_not_be_offered_Complimentary') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").hide();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'conversion_report_treatment') {
                $("#date_range_e").show();
                $("#month_e").hide();
                $("#year_e").hide();
                $("#day_number").hide();
                $("#regions").show();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').show();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').show();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'conversion_report_consultancy') {
                $("#date_range_e").show();
                $("#month_e").hide();
                $("#year_e").hide();
                $("#day_number").hide();
                $("#regions").show();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').show();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').show();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').show();
            } else if (type_p == 'dar_report') {
                $("#date_range_e").show();
                $("#month_e").hide();
                $("#year_e").hide();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').show();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'complimentory_report') {
                $("#date_range_e").show();
                $("#month_e").hide();
                $("#year_e").hide();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'dtr_report') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            } else if (type_p == 'center_target_report') {
                $("#date_range_e").hide();
                $("#month_e").show();
                $("#year_e").show();
                $("#day_number").show();
                $("#regions").show();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').show();
                $('#consultancy_type_C').hide();
            } else {
                $("#date_range_e").show();
                $("#month_e").hide();
                $("#year_e").hide();
                $("#day_number").hide();
                $("#regions").hide();
                $("#city").hide();
                $("#locations").show();
                $('#services').hide();
                $('#users').hide();
                $('#patients').hide();
                $('#type_C').hide();
                $('#appointment_type_C').hide();
                $('#date_range_by_E').hide();
                $('#date_range_by_L').hide();
                $('#completed_working_days_E').hide();
                $('#consultancy_type_C').hide();
            }
        });
        $('#report_type').change();
    </script>
    <script></script>

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
    <script src="{{ url('js/admin/reports/operations/general.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection