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
    <h1 class="page-title">@lang('global.reports.ledger_report')</h1>
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
                        <div class="form-group col-md-2 sn-select">
                            {!! Form::label('location_id', 'Centres', ['class' => 'control-label']) !!}
                            <div class="input-group">
                                {!! Form::select('location_id', $locations, '', ['id' => 'location_id' ,'class' => 'form-control form-filter input-sm select2',]) !!}
                            </div>
                        </div>
                        {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                        <div class="form-group col-md-2 sn-select @if($errors->has('report_type')) has-error @endif">
                            {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                            <select name="report_type" id="report_type" style="width: 100%"
                                    class="form-control select2">
                                @if(Gate::allows('finance_ledger_reports_Customer_payment_ledger_all_entries'))
                                    <option value="default">Select a Report Type</option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_Customer_payment_ledger_all_entries'))
                                    <option value="Customer_payment_ledger_all_entries">Customer Payment Ledger</option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_customer_treatment_package_ledger'))
                                    <option value="customer_treatment_package_ledger">Customer Treatment Package Ledger</option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_plan_maturity'))
                                    <option value="plan_maturity">Plan Maturity Report</option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_list_of_advances_as_of_today'))
                                    <option value="list_of_advances_as_of_today">List of Advances as of Today</option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_list_of_outstanding_as_of_today'))
                                    <option value="list_of_outstanding_as_of_today">List of Outstanding as of Today
                                    </option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_Summarized_data_of_Discounts_given_to_the_customer'))
                                    <option value="Summarized_data_of_Discounts_given_to_the_customer">Summarized Data
                                        of Discounts given to the Customer
                                    </option>
                                @endif
                                @if(Gate::allows('finance_ledger_reports_List_of_Clients_who_claimed_refunds'))
                                    <option value="List_of_Clients_who_claimed_refunds">List of Clients Who Claimed
                                        Refunds
                                    </option>
                                @endif
                            </select>
                            <span id="report_type_handler"></span>
                        </div>
                        <div style="display: none;" id="type_C"
                             class="form-group col-md-2 sn-select @if($errors->has('type')) has-error @endif">
                            {!! Form::label('type', 'Type*', ['class' => 'control-label']) !!}
                            {!! Form::select('type', array(''=>'Select Type','plan' => 'Plan', 'nonplan' => 'Non plan'), null, ['id' => 'type', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                            <span id="report_type_handler"></span>
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
                    {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.ledger_reports_load_report'], 'id' => 'report-form']) !!}
                    {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                    {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                    {!! Form::hidden('location_id', null, ['id' => 'location_id-report']) !!}
                    {!! Form::hidden('type', null, ['id' => 'type-report']) !!}
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
            if (type_p == 'list_of_advances_as_of_today' || type_p == 'list_of_outstanding_as_of_today' || type_p == 'List_of_Clients_who_claimed_refunds') {
                $("#type_C").show();
            } else {
                $("#type_C").hide();
            }
        });
        $('#report_type').change();


    </script>
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
    <script src="{{ url('js/admin/reports/ledger/general.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/users/ajaxbaseselect2id.js') }}" type="text/javascript"></script>
@endsection