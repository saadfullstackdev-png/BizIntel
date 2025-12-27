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
    <h1 class="page-title">@lang('global.reports.staff_listing')</h1>
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
                        <div class="form-group col-md-2 sn-select @if($errors->has('staff_type')) has-error @endif">
                            {!! Form::label('staff_type', 'Staff Type', ['class' => 'control-label']) !!}
                            {!! Form::select('staff_type', $usersListData, null, ['id' => 'staff_type', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif user_id">
                            {!! Form::label('user_id', 'All Staff', ['class' => 'control-label']) !!}
                            {!! Form::select('user_id', $staff, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2 user_id']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif doctor_id">
                            {!! Form::label('doctor_id', 'Practitioner Staff', ['class' => 'control-label']) !!}
                            {!! Form::select('user_id', $practionars, "", ['id' => 'doctor_id', 'style' => 'width: 100%;', 'class' => 'form-control select2 doctor_id']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif app_user_id">
                            {!! Form::label('app_user_id', 'Application User Staff', ['class' => 'control-label']) !!}
                            {!! Form::select('user_id', $application_user, "", ['id' => 'app_user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2 app_user_id']) !!}
                            <span id="user_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('email')) has-error @endif">
                            {!! Form::label('email_id', 'Email', ['class' => 'control-label']) !!}
                            <input type="text" class="form-control form-filter input-sm" name="email" id="email"
                                   placeholder="Enter Email">
                            <span id="email"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('gender')) has-error @endif">
                            {!! Form::label('gender_id', 'Gender', ['class' => 'control-label']) !!}
                            {!! Form::select('gender_id', array('' => 'Select a Gender') + Config::get("constants.gender_array"), (old('gender')) ? old('gender') : '',['id' => 'gender_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="gender_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('region_id')) has-error @endif">
                            {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
                            {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="region_id_handler"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-2 sn-select @if($errors->has('location_id')) has-error @endif">
                            {!! Form::label('location_id', 'Centre', ['class' => 'control-label']) !!}
                            {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="location_id_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('telecomprovider')) has-error @endif">
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
                        <div class="form-group col-md-2 sn-select @if($errors->has('phone')) has-error @endif">
                            {!! Form::label('Phone_id', 'Phone Number', ['class' => 'control-label']) !!}
                            <input type="text" class="form-control form-filter input-sm" name="phone" id="phone"
                                   placeholder="Enter Phone">
                            <span id="phone"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                            {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                            <select name="report_type" id="report_type" style="width:100%;"
                                    class="form-control select2">
                                @if(Gate::allows('staff_listing_reports_region_wise_staff_list'))
                                    <option value="default">Select a Report Type</option>
                                @endif
                                @if(Gate::allows('staff_listing_reports_region_wise_staff_list'))
                                    <option value="region_wise_staff_list">Region Wise Staff List</option>

                                @endif
                                @if(Gate::allows('staff_listing_reports_centre_wise_staff_list'))
                                    <option value="centre_wise_staff_list">Centre Wise Staff List</option>
                                @endif
                            </select>
                            <span id="report_type_handler"></span>
                        </div>
                        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                        <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                            {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                            <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                               class="btn btn-success">Load Report</a>
                        </div>
                    </div>
                    <div class="clear clearfix"></div>
                    <div id="content"></div>

                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.staff.reports.load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                    {!! Form::hidden('email', null, ['id' => 'email-report']) !!}
                    {!! Form::hidden('gender_id', null, ['id' => 'gender_id-report']) !!}
                    {!! Form::hidden('age_group_range', null, ['id' => 'age_group_range-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('lead_status_id', null, ['id' => 'lead_status_id-report']) !!}
                    {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                    {!! Form::hidden('telecomprovider_id', null, ['id' => 'telecomprovider_id-report']) !!}
                    {!! Form::hidden('phone', null, ['id' => 'phone-report']) !!}
                    {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                    {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
@section('javascript')
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
    <script src="{{ url('js/admin/reports/staff/staff.js') }}" type="text/javascript"></script>
@endsection