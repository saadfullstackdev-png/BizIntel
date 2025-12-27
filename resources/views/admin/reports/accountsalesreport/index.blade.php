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
    @if (!auth()->user()->hasRole('Center Manager'))
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
          rel="stylesheet" type="text/css"/>
    @endif
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        #service_id span.select2-container {
            z-index: 10050;
        }
    </style>
@stop
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.reports.revenue_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-body sn-panel">
            <div class="box box-primary">
                <div class="panel-body pad table-responsive">
                    <div class="form-group col-md-2 sn-select @if($errors->has('date_range')) has-error @endif">
                        {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </div>
                            {{-- @dd(auth()->user()->hasRole('Center Manager')); --}}
                            @php
                                use Carbon\Carbon;
                                $today = Carbon::now()->format('m/d/Y');
                            @endphp

                            @if (auth()->user()->hasRole('Center Manager'))
                                {!! Form::text('date_range', "$today - $today", [
                                    'id' => 'date_range',
                                    'class' => 'form-control',
                                    'readonly' => true
                                ]) !!}
                            @else
                                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                            @endif
                        </div>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                        {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                        <select name="report_type" id="report_type" style="width:100%" class="form-control select2">
                            @if(Gate::allows('finance_general_revenue_reports_center_performance_stats_by_revenue_finance'))
                                <option value="default">Select a Report Type</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_center_performance_stats_by_revenue_finance'))
                                <option value="center_performance_stats_by_revenue">Center performance stats by
                                    Revenue
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_center_performance_stats_by_service_type_finance'))
                                <option value="center_performance_stats_by_service_type">Center performance stats by
                                    Service Type
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_account_sales_report'))
                                <option value="account_sales_report">Account Sales Report</option>
                            @endif

                            @if(Gate::allows('finance_general_revenue_reports_collection_by_service'))
                                <option value="collection_by_service">Collection by Service</option>
                            @endif

                            @if(Gate::allows('finance_general_revenue_reports_daily_employee_stats_summary'))
                                <option value="daily_employee_stats_summary">Sale Summary Service Wise</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_daily_employee_stats'))
                                <option value="daily_employee_stats">Sale Summary Doctors Wise</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_sales_by_service_category'))
                                <option value="sales_by_service_category">Sale Summary Category Wise</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_discount_report'))
                                <option value="discount_report">Discount Report</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_general_revenue__detail_report'))
                                <option value="general_revenue_report_detail">General Revenue Detail Report</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_general_revenue__summary_report'))
                                <option value="general_revenue_report_summary">General Revenue Summary Report</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_pabau_record_revenue_report'))
                                <option value="pabau_record_revenue_report">Pabau Record Revenue Report</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_machine_wise_collection_report'))
                                <option value="machine_wise_collection_report">Machine wise Collection Report</option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_machine_wise_invoice_revenue_report'))
                                <option value="machine_wise_invoice_revenue_report">Machine wise Invoice Revenue
                                    Report
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_partner_collection_report'))
                                <option value="partner_collection_report">Partner Collection Report
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_staff_wise_revenue'))
                                <option value="staff_wise_revenue">Staff Wise Revenue
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_conversion_report'))
                                <option value="conversion_report">Conversion Report
                                </option>
                            @endif
                            @if(Gate::allows('finance_general_revenue_reports_consume_plan_revenue_report'))
                                <option value="consume_plan_revenue_report">Consume Plan Revenue Report
                                </option>
                            @endif
                        </select>
                        <span id="report_type_handler"></span>
                    </div>
                    <div class="form-group col-md-4 sn-select @if($errors->has('patient_id')) has-error @endif"
                         id="patient_id_E">
                        {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                        <select name="patient_id" id="patient_id" class="form-control patient_id"></select>
                        <span id="patient_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('appointment_type_id')) has-error @endif"
                         id="appointment_type_id_E">
                        {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
                        {!! Form::select('appointment_type_id', $appointment_types, null, ['onchange' => 'getDiscounts()','id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="appointment_type_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 @if($errors->has('discount_id')) has-error @endif" id="discount"
                         style="display: none;">
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('city_id')) has-error @endif"
                         id="city_id_E">
                        {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                        {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="city_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('region_id')) has-error @endif"
                         id="region_id_E">
                        {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
                        {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="region_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif"
                         id="location_id_E">
                        {!! Form::label('location_id', 'Centres', ['class' => 'control-label']) !!}
                        {!! Form::select('location_id', $locations, null, ['onchange' => 'loadmachine();' , 'id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2 sn-select']) !!}
                        <span id="location_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('machine')) has-error @endif"
                         id="machine"
                         style="display: none;">
                    </div>
                    {{--For General Revenue report we need *location so that why I repeat--}}
                    <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif"
                         style="display: none;" id="location_id_D">
                        {!! Form::label('location_id_com', 'Centres', ['class' => 'control-label']) !!}
                        {!! Form::select('location_id_com[]', $locations_com, null, ['id' => 'location_id_com','class' => 'form-control select2', 'multiple' => 'multiple']) !!}
                        <span id="location_id_handler"></span>
                    </div>
                    {{--For Personal Detail--}}
                    {{--<div class="form-group col-md-2 sn-select @if($errors->has('personal_datai_id')) has-error @endif"
                         id="personal_detail_id_E">
                        {!! Form::label('personal_detail_id', 'Personal Detail', ['class' => 'control-label']) !!}
                        {!! Form::select('personal_detail_id',[''=>'Select','show_personal_detail'=>'Show Personal Detail','not_show_personal_detail'=>'Not Show Personal Detail'],null,['id' => 'personal_detail_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="personal_datai_id_handler"></span>
                    </div>--}}
                    {{--End--}}
                    {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                    <div class="form-group col-md-2 sn-select @if($errors->has('service_id')) has-error @endif"
                         id="service_id_E">
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

                    <div class="form-group col-md-2 sn-select @if($errors->has('appointment_type_id')) has-error @endif"
                         id="user_id_E">
                        {!! Form::label('user_id', 'Employee', ['class' => 'control-label']) !!}
                        {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="user_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('doctor_id')) has-error @endif"
                         style="display: none" id="doctors_id">
                        {!! Form::label('doctor_id', 'Doctor', ['class' => 'control-label']) !!}
                        {!! Form::select('doctor_id', $operators, null, ['id' => 'doctor_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="doctor_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                        {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                        <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                           class="btn btn-success">Load Report</a>
                    </div>

                    <div class="clear clearfix"></div>
                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.account_sales_report_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('location_id_com', null, ['id' => 'location_id_com-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                    {!! Form::hidden('doctor_id', null, ['id' => 'doctor_id-report']) !!}
                    {!! Form::hidden('machine_id', null, ['id' => 'machine_id-report']) !!}
                    {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                    {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                    {!! Form::hidden('converted', null, ['id' => 'converted_type-report']) !!}
                    {!! Form::hidden('discount_id', '', ['id' => 'discount_id-report']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
    <script src="{{ url('js/admin/reports/finance/loadmachine.js') }}" type="text/javascript"></script>
    @if (auth()->user()->hasRole('Center Manager'))
    <script>
            var FormControls = function () {
                //== Private functions

                var baseFunction = function () {
                    $('.select2').select2({ width: '100%' });

                    $('#date_range').daterangepicker({
                        alwaysShowCalendars: false,
                        showDropdowns: false,
                        opens: 'center',
                        drops: 'auto',
                        locale: {
                            format: 'MM/DD/YYYY',
                        },
                        ranges: {
                            'Today': [moment(), moment()]
                        },
                        startDate: moment(),
                        endDate: moment()
                    }, function(start, end, label) {
                        // Force reset to today's date if user tries to change
                        $('#date_range').val(moment().format('MM/DD/YYYY') + ' - ' + moment().format('MM/DD/YYYY'));
                    });

                    // Lock the input to prevent user interaction
                    $('#date_range').attr('readonly', true).css('pointer-events', 'none');


                    $('#scheduled_date').daterangepicker({
                        "alwaysShowCalendars": true,
                        locale: {
                            // cancelLabel: 'Clear'
                        },
                        ranges   : {
                            'Today'       : [moment(), moment()],
                            'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                            'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                            'This Year'  : [moment().startOf('year'), moment().endOf('year')],
                            'Last Year'  : [moment().subtract(1, 'year').startOf('month'), moment().subtract(1, 'year').endOf('year')],
                        },
                        startDate: moment().subtract(29, 'days'),
                        endDate  : moment()
                    });

                    $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                    });

                    $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
                        // $(this).val('');
                    });
                }

                var loadReport = function () {
                    $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled',true);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: route('admin.reports.account_sales_report_load'),
                        type: "POST",
                        data: {
                            date_range: $('#date_range').val(),
                            patient_id: $('#patient_id').val(),
                            appointment_type_id: $('#appointment_type_id').val(),
                            location_id: $('#location_id').val(),
                            location_id_com: $('#location_id_com').val(),
                            region_id: $('#region_id').val(),
                            service_id: $('#service_id').val(),
                            user_id: $('#user_id').val(),
                            doctor_id: $('#doctor_id').val(),
                            medium_type: $('#medium_type').val(),
                            report_type: $('#report_type').val(),
                            city_id: $('#city_id').val(),
                            machine_id: $('#machine_id').val(),
                            discount_id:$('#discount_id').val()
                        },
                        success: function(response){
                            $('#content').html('');
                            if($('#medium_type').val() == 'web') {
                                $('#content').html(response);
                            } else {
                                return false;
                                // loadChart(response.start_date, response.end_date, response.SaleData);
                            }
                            $('#load_report').html('Load Report').removeAttr('disabled');
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            $('#load_report').html('Load Report').removeAttr('disabled');
                            return false;
                        }
                    });
                }

                var loadChart = function (start, end, data) {
                    return false;
                    var categories = [];
                    var total_prices = [];
                    var total_qtys = [];
                    for(loop =0; loop < data.length; loop++) {
                        categories.push(data[loop].sale_date + ' - ' + data[loop].customer_name);
                        total_prices.push(parseFloat(data[loop].total_price));
                        total_qtys.push(parseInt(data[loop].total_qty));
                    }

                    Highcharts.chart('content', {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: 'Sale Report by Customer'
                        },
                        xAxis: {
                            categories: categories,
                            title: {
                                text: null
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Population (millions)',
                                align: 'high'
                            },
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        tooltip: {
                            valueSuffix: ''
                        },
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    enabled: true
                                }
                            }
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'top',
                            x: -40,
                            y: 80,
                            floating: true,
                            borderWidth: 1,
                            backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                            shadow: true
                        },
                        credits: {
                            enabled: false
                        },
                        series: [{
                            name: 'Saled Qty',
                            data: total_qtys
                        }, {
                            name: 'Saled Price',
                            data: total_prices
                        }]
                    });
                }

                var printReport = function (medium_type) {
                    $('#date_range-report').val($('#date_range').val());
                    $('#patient_id-report').val($('#patient_id').val());
                    $('#appointment_type_id-report').val($('#appointment_type_id').val());
                    $('#location_id-report').val($('#location_id').val());
                    $('#location_id_com-report').val($('#location_id_com').val());
                    $('#machine_id-report').val($('#machine_id').val());
                    $('#region_id-report').val($('#region_id').val());
                    $('#service_id-report').val($('#service_id').val());
                    $('#user_id-report').val($('#user_id').val());
                    $('#doctor_id-report').val($('#doctor_id').val());
                    $('#medium_type-report').val(medium_type);
                    $('#report_type-report').val($('#report_type').val());
                    $('#discount_id-report').val($('#discount_id').val());
                    $('#report-form').submit();
                }

                return {
                    // public functions
                    init: function() {
                        baseFunction();
                    },
                    loadReport: loadReport,
                    printReport: printReport,
                };
            }();

            jQuery(document).ready(function() {
                FormControls.init();
            });
    </script>
    @endif
    <script>
        $(document).on('change', '#report_type', function () {
            var type_p = $("#report_type").val();
            $('#city_id_E').hide();
            if (type_p == 'general_revenue_report_detail') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").show();
                $("#location_id_E").hide();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").hide();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'sales_by_service_category') {
                $("#machine").hide();
                $('#discount').hide();
                $("#doctors_id").hide();
                $("#appointment_type_id_E").show();
                $("#user_id_E").hide();
                $("#patient_id_E").show();
                $("#location_id_E").show();
                $("#location_id_D").hide();
                $("#service_id_E").show();
            } else if (type_p == 'daily_employee_stats_summary') {
                $("#machine").hide();
                $('#discount').hide();
                $("#doctors_id").hide();
                $("#user_id_E").hide();
                $("#appointment_type_id_E").show();
                $("#patient_id_E").show();
                $("#location_id_E").show();
                $("#location_id_D").hide();
                $("#service_id_E").show();
            } else if (type_p == 'general_revenue_report_summary') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").hide();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").show();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'pabau_record_revenue_report') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").hide();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'machine_wise_invoice_revenue_report' || type_p == 'machine_wise_collection_report') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").show();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'partner_collection_report') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").hide();
                $("#doctors_id").hide();
                $('#discount').hide();
            } else if (type_p == 'staff_wise_revenue') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").hide();
                $("#doctors_id").show();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'conversion_report') {
                $("#location_id_E").show();
                $("#location_id_D").hide();
                $("#patient_id_E").show();
                $("#user_id_E").hide();
                $("#doctors_id").show();
                $("#region_id_E").show();
                $("#city_id_E").show();
                $("#service_id_E").show();
                $("#appointment_type_id_E").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == "collection_by_service") {
                $("#location_id_E").show();
                $("#location_id_D").hide();
                $("#patient_id_E").hide();
                $("#user_id_E").hide();
                $("#doctors_id").hide();
                $("#region_id_E").show();
                $("#city_id_E").hide();
                $("#service_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'daily_employee_stats') {
                $("#patient_id_E").show();
                $("#appointment_type_id_E").show();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").show();
                $("#region_id_E").hide();
                $("#doctors_id").show();
                $("#machine").hide();
                $('#discount').hide();
            } else if (type_p == 'consume_plan_revenue_report') {
                $("#patient_id_E").hide();
                $("#appointment_type_id_E").hide();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").show();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            } else {
                $("#patient_id_E").show();
                $("#appointment_type_id_E").show();
                $("#location_id_D").hide();
                $("#location_id_E").show();
                $("#user_id_E").show();
                $("#service_id_E").show();
                $("#region_id_E").hide();
                $("#doctors_id").hide();
                $("#machine").hide();
                $('#discount').hide();
            }
        });
        $('#report_type').change();
    </script>


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
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"
            type="text/javascript"></script>
    @if (!auth()->user()->hasRole('Center Manager'))
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/reports/finance/general.js') }}" type="text/javascript"></script>
    @endif
    <script src="{{ url('metronic/assets/global/scripts/app.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/pages/scripts/components-date-time-pickers.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection