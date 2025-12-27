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
        /*#service_id span.select2-container {*/
            /*z-index: 10050;*/
        /*}*/
    @page {
        margin: 10px 20px;
    }

    @media print {
        table {
            font-size: 12px;
        }

        .tr-root-group {
            background-color: #F3F3F3;
            color: rgba(0, 0, 0, 0.98);
            font-weight: bold;
        }

        .tr-group {
            font-weight: bold;
        }

        .bold-text {
            font-weight: bold;
        }

        .error-text {
            font-weight: bold;
            color: #FF0000;
        }

        .ok-text {
            color: #006400;
        }
    }
    </style>
@stop
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.reports.dashboard_report')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="sn-table-holder">
        <div class="sn-report-head">
            <div class="sn-title">
                <h1>{{($performance == 'true')?'My Appointment By Type':'Appointment By Type'}}</h1>
            </div>
            <?php if ($performance == 'true') {
                $my_collection = 'true';
            } else {
                $my_collection = 'false';
            } ?>
            <div class="sn-buttons">
                @if( $medium_type == 'web')
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboadReport.appointmentbytype',['excel',$my_collection,$period]) }}" target="_blank">
                        <i class="fa fa-file-excel-o"></i><span>Excel</span>
                    </a>
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboadReport.appointmentbytype',['pdf',$my_collection,$period]) }}"
                       target="_blank">
                        <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                    </a>
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboadReport.appointmentbytype',['print',$my_collection,$period]) }}"
                       target="_blank">
                        <i class="fa fa-print"></i><span>Print</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
    <div class="panel-body sn-table-body">
        <div class="bordered">
            <div class="sn-table-head">
                <div class="row">
                    <div class="col-md-2">
                        <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
                    </div>
                    <div class="col-md-6">&nbsp;</div>
                    <div class="col-md-4">
                        <table class="dark-th-table table table-bordered">
                            <tr>
                                <th width="25%">Duration</th>
                                <td>From {{ $start_date }} to {{ $end_date }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div><!-- End sn-table-head -->
            <div class="table-wrapper" id="topscroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Scheduled</th>
                            <th>Doctor</th>
                            <th>City</th>
                            <th>Centre</th>
                            <th>Treatment/Consultancy</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Rescheduled By</th>
                            <th>Referred By</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(count($reportData))
                        @foreach($reportData as $reporttype)
                            @php
                                $count = 0;
                            @endphp
                            <tr style="font-weight: bold">
                                <td>{{$reporttype['type_name']}}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>

                            </tr>
                            @foreach($reporttype['appointment_data'] as $appointmentdata)
                                <tr>
                                    <td>{{ $appointmentdata['appointment_id'] }}</td>
                                    <td>{{ $appointmentdata['patient_name'] }}</td>
                                    <td>{{ \App\Helpers\GeneralFunctions::prepareNumber4Call($appointmentdata['patient_phone']) }}</td>
                                    <td>{{ $appointmentdata['patient_email'] }}</td>
                                    <td>{{ $appointmentdata['scheduled_at'] }}</td>
                                    <td>{{ $appointmentdata['doctor_name'] }}</td>
                                    <td>{{ $appointmentdata['city'] }}</td>
                                    <td>{{ $appointmentdata['centre'] }}</td>
                                    <td>{{ $appointmentdata['consultancy'] }}</td>
                                    <td>{{ $appointmentdata['status'] }}</td>
                                    <td>{{ $appointmentdata['type'] }}</td>
                                    <td>{{ $appointmentdata['created_at'] }}</td>
                                    <td>{{ $appointmentdata['created_by'] }}</td>
                                    <td>{{ $appointmentdata['converted_by'] }}</td>
                                    <td>{{ $appointmentdata['rescheduled_by'] }}</td>
                                    <td>{{ $appointmentdata['referred_by'] }}</td>
                                    @php
                                        $count++
                                    @endphp
                                </tr>
                            @endforeach
                            <tr style="background-color:#364150;color: #fff;">
                                <td style="color: #fff">Total</td>
                                <td style="color: #fff">{{number_format($count)}}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="14" align="center">No record round.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
        </div>
    </div>
<div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
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
    <script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>
    <script>
        function DoubleScroll(element) {
            var scrollbar = document.createElement('div');
            scrollbar.className = 'fake-scroll';
            scrollbar.appendChild(document.createElement('div'));
            scrollbar.style.overflow = 'auto';
            scrollbar.style.overflowY = 'hidden';
            scrollbar.firstChild.style.width = element.scrollWidth + 'px';
            scrollbar.firstChild.style.paddingTop = '1px';
            scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
            scrollbar.onscroll = function () {
                element.scrollLeft = scrollbar.scrollLeft;
            };
            element.onscroll = function () {
                scrollbar.scrollLeft = element.scrollLeft;
            };
            element.parentNode.insertBefore(scrollbar, element);
        }

        DoubleScroll(document.getElementById('topscroll'));
    </script>
@endsection