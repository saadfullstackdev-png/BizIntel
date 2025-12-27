@inject('request', 'Illuminate\Http\Request')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css'}}" rel="stylesheet"
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
    <h1 class="page-title">@lang('global.reports.lead_report')</h1>
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
                            {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                        {!! Form::label('report_type', 'Report Type', ['class' => 'control-label']) !!}
                        <select name="report_type" id="report_type" style="width:100%;"
                                class="form-control select2">
                            @if(Gate::allows('leads_reports_general_report'))
                                <option value="default">Select a Report Type</option>
                            @endif
                            @if(Gate::allows('leads_reports_general_report'))
                                <option value="generalreport">General Report</option>
                            @endif
                            @if(Gate::allows('leads_reports_summary_report_by_lead_status'))
                                <option value="summaryreport">Summary Report By Lead Status</option>
                            @endif
                            @if(Gate::allows('leads_reports_lead_status_percentage'))
                                <option value="lead_status_ratio">Lead Status Percentage</option>
                            @endif
                            @if(Gate::allows('leads_reports_now_show_report'))
                                <option value="now_show_report">Now Show List Report</option>
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
                    <div class="form-group col-md-2 sn-select @if($errors->has('cnic')) has-error @endif" id="cnic_E">
                        {!! Form::label('cnic', 'CNIC', ['class' => 'control-label']) !!}
                        {!! Form::text('cnic',null,['id' => 'cnic', 'style' => 'width: 100%;' ,'class' => 'form-control', 'placeholder' => '']) !!}
                        <span id="patient_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('dob')) has-error @endif" id="dob_E">
                        {!! Form::label('dob', 'DOB', ['class' => 'control-label']) !!}
                        {!! Form::text('dob',null,['readonly' => true, 'id' => 'dob', 'class' => 'form-control dob', 'placeholder' => '']) !!}
                        <span id="patient_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('email')) has-error @endif" id="email_E">
                        {!! Form::label('email_id', 'Email', ['class' => 'control-label']) !!}
                        <input type="text" class="form-control form-filter input-sm" name="email" id="email"
                               placeholder="Enter Email">
                        <span id="email"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('gender')) has-error @endif"
                         id="gender_E">
                        {!! Form::label('gender_id', 'Gender', ['class' => 'control-label']) !!}
                        {!! Form::select('gender_id', array('' => 'Select a Gender') + Config::get("constants.gender_array"), (old('gender')) ? old('gender') : '',['id' => 'gender_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="gender_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('agegroup')) has-error @endif"
                         id="age_group_range_E">
                        {!! Form::label('age_group_range', 'Age Group', ['class' => 'control-label']) !!}
                        <select name="age_group_range" id="age_group_range" style="width: 100%"
                                class="form-control select2">
                            <option value="">Select Age Range</option>
                            <option value="10:20">10-20</option>
                            <option value="20:30">20-30</option>
                            <option value="30:40">30-40</option>
                            <option value="40:50">40-50</option>
                            <option value="50:60">50-60</option>
                            <option value="60:70">60-70</option>
                            <option value="80:300">80+</option>
                        </select>
                        <span id="age_group_range_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('region_id')) has-error @endif"
                         id="region_id_E">
                        {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
                        {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="region_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('city_id')) has-error @endif"
                         id="city_id_E">
                        {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                        {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="city_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('town_id')) has-error @endif"
                         id="town_id_E">
                        {!! Form::label('town_id', 'Town', ['class' => 'control-label']) !!}
                        {!! Form::select('town_id', $towns, null, ['id' => 'town_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="town_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif"
                         id="location_id_E">
                        {!! Form::label('location_id', 'Select Center', ['class' => 'control-label']) !!}
                        {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="location_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('lead_status_id')) has-error @endif"
                         id="lead_status_id_E">
                        {!! Form::label('lead_status_id', 'Lead Status', ['class' => 'control-label']) !!}
                        {!! Form::select('lead_status_id', $lead_statuses, null, ['id' => 'lead_status_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="lead_status_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('lead_sources_id')) has-error @endif"
                         id="lead_sources_id_E">
                        {!! Form::label('lead_sources_id', 'Lead Sources', ['class' => 'control-label']) !!}
                        {!! Form::select('lead_sources_id', $lead_sources, null, ['id' => 'lead_sources_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="lead_sources_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('service_id')) has-error @endif"
                         id="service_id_E">
                        {!! Form::label('service_id', 'Services', ['class' => 'control-label']) !!}
                        <select class="form-control form-filter input-sm select2" name="service_id" id="service_id"
                                style="width: 100%;">
                            <option value="">Select Service</option>
                            @foreach($Services as $id => $Service)
                                @if ($id == 0)
                                    @continue;
                                @endif
                                @if($id < 0)
                                    @php($tmp_id = ($id * -1))
                                @else
                                    @php($tmp_id = ($id * 1))
                                @endif
                                <option @if($tmp_id==$leadServices) selected="selected"
                                        @endif value="@if($id < 0){{ ($id * -1) }}@else{{ $id }}@endif">@if($id < 0)
                                        <b>{!! $Service['name'] !!}</b>
                                    @else
                                        {!! $Service['name'] !!}
                                    @endif</option>
                            @endforeach
                        </select>
                        <span id="service_id_handler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('telecomprovider')) has-error @endif"
                         id="telecomprovider_id_E">
                        {!! Form::label('telecomprovider_id', 'Telecomprovider', ['class' => 'control-label']) !!}
                        <select name="telecomprovider_id" id="telecomprovider_id" class="form-control select2"
                                multiple>
                            <option value="">Select Telecomprovider</option>
                            @foreach($telcomprovider as $telecom)
                                <optgroup label="{{$telecom['name']}}">
                                    @foreach($telecom['children'] as $telenumber)
                                        <option value="{{$telenumber['id']}}"><?php echo $telenumber['pre_fix']; ?></option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <span id="telecomprovider_id_hundler"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('phone')) has-error @endif"
                         id="Phone_id_E">
                        {!! Form::label('Phone_id', 'Phone Number', ['class' => 'control-label']) !!}
                        <input type="text" class="form-control form-filter input-sm" name="phone" id="phone"
                               placeholder="Enter Phone">
                        <span id="phone"></span>
                    </div>
                    <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif"
                         id="user_id_E">
                        {!! Form::label('Create_id', 'Created By', ['class' => 'control-label']) !!}
                        {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                        <span id="user_id_handler"></span>
                    </div>
                    {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                    <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                        {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                        <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                           class="btn btn-success">Load Report</a>
                    </div>
                    <div class="clear clearfix"></div>
                    <div id="content"></div>
                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.leads.leads_reports_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('cnic', null, ['id' => 'cnic-report']) !!}
                    {!! Form::hidden('dob', null, ['id' => 'dob-report']) !!}
                    {!! Form::hidden('email', null, ['id' => 'email-report']) !!}
                    {!! Form::hidden('gender_id', null, ['id' => 'gender_id-report']) !!}
                    {!! Form::hidden('age_group_range', null, ['id' => 'age_group_range-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('town_id', null, ['id' => 'town_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('lead_status_id', null, ['id' => 'lead_status_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('telecomprovider_id', null, ['id' => 'telecomprovider_id-report']) !!}
                    {!! Form::hidden('phone', null, ['id' => 'phone-report']) !!}
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
    <script>
        $(document).on('change', '#report_type', function () {
            var type_p = $("#report_type").val();
            if (type_p == 'summaryreport' || type_p == 'lead_status_ratio') {
                $("#patient_id_E").hide();
                $("#cnic_E").hide();
                $("#dob_E").hide();
                $("#email_E").hide();
                $("#gender_E").hide();
                $("#age_group_range_E").hide();
                $("#lead_sources_id_E").hide();
                $("#lead_status_id_E").hide();
                $("#telecomprovider_id_E").hide();
                $("#Phone_id_E").hide();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").show();
                $("#city_id_E").show();
                $("#town_id_E").hide();
                $("#location_id_E").hide();
            } else if (type_p == 'now_show_report') {
                $("#patient_id_E").hide();
                $("#cnic_E").hide();
                $("#dob_E").hide();
                $("#email_E").hide();
                $("#gender_E").hide();
                $("#age_group_range_E").hide();
                $("#lead_sources_id_E").hide();
                $("#lead_status_id_E").hide();
                $("#telecomprovider_id_E").hide();
                $("#Phone_id_E").hide();
                $("#user_id_E").hide();
                $("#service_id_E").hide();
                $("#region_id_E").hide();
                $("#city_id_E").hide();
                $("#town_id_E").hide();
                $("#location_id_E").hide();
            } else {
                $("#patient_id_E").show();
                $("#cnic_E").show();
                $("#dob_E").show();
                $("#email_E").show();
                $("#gender_E").show();
                $("#age_group_range_E").show();
                $("#lead_sources_id_E").show();
                $("#lead_status_id_E").show();
                $("#telecomprovider_id_E").show();
                $("#Phone_id_E").show();
                $("#user_id_E").show();
                $("#service_id_E").show();
                $("#region_id_E").show();
                $("#city_id_E").show();
                $("#town_id_E").show();
                $("#location_id_E").show();
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
    <script src="{{ url('js/admin/reports/leads/leads.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection