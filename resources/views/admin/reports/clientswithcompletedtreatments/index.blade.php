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
    <h1 class="page-title">@lang('global.reports.centers_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-body sn-panel">
            <div class="box box-primary">
                <div class="panel-body pad table-responsive">
                    <div class="row">
                        <div class="form-group col-md-2 sn-select  @if($errors->has('date_range')) has-error @endif">
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
                        <div class="form-group col-md-2  sn-select @if($errors->has('location_id')) has-error @endif"
                             id="location_id_e">
                            {!! Form::label('location_id', 'Centres', ['class' => 'control-label']) !!}
                            {!! Form::select('location_id', $locations, '', ['id' => 'location_id' ,'class' => 'form-control form-filter input-sm select2',]) !!}
                            <span id="location_id_handler"></span>
                        </div>
                        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}

                        <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                            {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                            <select name="report_type" id="report_type" style="width: 100%;" class="form-control select2">
                                @if(Gate::allows('centers_reports_client_with_Completed_treatment'))
                                    <option value="default">Select a Report Type</option>
                                @endif
                                @if(Gate::allows('centers_reports_client_with_Completed_treatment'))
                                    <option value="client_with_Completed_treatment">Clients with completed treatments
                                    </option>
                                @endif
                                @if(Gate::allows('centers_reports_client_with_not_Completed_treatment'))
                                    <option value="client_with_not_Completed_treatment">Clients with not completed
                                        treatments
                                    </option>
                                @endif
                                @if(Gate::allows('centers_reports_clients_took_treatments_particular_month'))
                                    <option value="clients_took_treatments_particular_month">Clients with treatments in
                                        a particular month
                                    </option>
                                @endif
                                @if(Gate::allows('centers_reports_clients_with_birthday_days'))
                                    <option value="clients_with_birthday_days">Clients with birthday + x days</option>
                                @endif
                            </select>
                            <span id="report_type_handler"></span>
                        </div>
                        <div class="form-group col-md-2 sn-select  @if($errors->has('group_id')) has-error @endif">
                            {!! Form::label('load_report', '&nbsp;', ['class' => 'control-label']) !!}<br/>
                            <a href="javascript:void(0);" onclick="FormControls.loadReport();" id="load_report"
                               class="btn btn-success">Load Report</a>
                        </div>
                    </div>
                    <div class="clear clearfix"></div>
                    <div id="content"></div>
                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.centers.centers_reports_load'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                    {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
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
    <script src="{{ url('js/admin/reports/centers/centers.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection