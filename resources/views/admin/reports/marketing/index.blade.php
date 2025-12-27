    @inject('request', 'Illuminate\Http\Request')
    @extends('layouts.app')

    @section('stylesheets')
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css"/>
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-editable/inputs-ext/address/address.css') }}" rel="stylesheet" type="text/css"/>
        <!-- END PAGE LEVEL PLUGINS -->

        <link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/>
        <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
        <style type="text/css">
            #service_id span.select2-container {
                z-index: 10050;
            }
        </style>
    @stop
    @section('title')
        <!-- BEGIN PAGE TITLE-->
        <h1 class="page-title">@lang('global.reports.marketing_report')</h1>
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
                            <div class="form-group col-md-2 sn-select @if($errors->has('cnic')) has-error @endif">
                                {!! Form::label('cnic', 'CNIC', ['class' => 'control-label']) !!}
                                {!! Form::text('cnic',null,['id' => 'cnic', 'style' => 'width: 100%;' ,'class' => 'form-control', 'placeholder' => '']) !!}
                                <span id="cnic_handler"></span>
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
                        </div>
                        <div class="row">
                            <div class="form-group col-md-2 sn-select @if($errors->has('region_id')) has-error @endif">
                                {!! Form::label('region_id', 'Region', ['class' => 'control-label']) !!}
                                {!! Form::select('region_id', $regions, null, ['id' => 'region_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                <span id="region_id_handler"></span>
                            </div>
                            <div class="form-group col-md-2 sn-select @if($errors->has('city_id')) has-error @endif">
                                {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                                {!! Form::select('city_id', $cities, null, ['id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                <span id="city_id_handler"></span>
                            </div>
                            <div class="form-group col-md-2 sn-select @if($errors->has('lead_status_id')) has-error @endif">
                                {!! Form::label('status_id', 'Lead Status', ['class' => 'control-label']) !!}
                                {!! Form::select('lead_status_id', $lead_statuses, null, ['id' => 'lead_status_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                <span id="lead_status_id_handler"></span>
                            </div>
                            <div class="form-group col-md-2 sn-select @if($errors->has('phone')) has-error @endif">
                                {!! Form::label('Phone_id', 'Phone Number', ['class' => 'control-label']) !!}
                                <input type="text" class="form-control form-filter input-sm" name="phone" id="phone"
                                       placeholder="Enter Phone">
                                <span id="phone"></span>
                            </div>
                            <div class="form-group col-md-2 sn-select @if($errors->has('user_id')) has-error @endif">
                                {!! Form::label('Create_id', 'Created By', ['class' => 'control-label']) !!}
                                {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                <span id="user_id_handler"></span>
                            </div>
                            {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                            <div class="form-group col-md-2 sn-select @if($errors->has('referred_id')) has-error @endif">
                                {!! Form::label('referred_id', 'Referred By', ['class' => 'control-label']) !!}
                                {!! Form::select('referred_id',$employees,null, ['id' => 'referred_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                @if($errors->has('referred_by'))
                                    <p class="help-block">
                                        {{ $errors->first('referred_by') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-2 sn-select @if($errors->has('group_id')) has-error @endif">
                                {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                                <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                                   class="btn btn-success">Load Report</a>
                            </div>
                        </div>
                        <div class="clear clearfix"></div>
                        <div id="content"></div>

                        {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.marketing.marketing_reports_load'], 'id' => 'report-form']) !!}
                        {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                        {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                        {!! Form::hidden('cnic', null, ['id' => 'cnic-report']) !!}
                        {!! Form::hidden('email', null, ['id' => 'email-report']) !!}
                        {!! Form::hidden('gender_id', null, ['id' => 'gender_id-report']) !!}
                        {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                        {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                        {!! Form::hidden('lead_status_id', null, ['id' => 'lead_status_id-report']) !!}
                        {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                        {!! Form::hidden('phone', null, ['id' => 'phone-report']) !!}
                        {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                        {!! Form::hidden('referred_id', null, ['id' => 'referred_id-report']) !!}
                        {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @stop
    @section('javascript')
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
        <script src="{{ url('js/admin/reports/marketing/general.js') }}" type="text/javascript"></script>
        <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
    @endsection