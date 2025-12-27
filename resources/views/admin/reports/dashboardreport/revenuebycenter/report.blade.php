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
        /*#service_id span.select2-container {
            z-index: 10050;
        }*/
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

    <style type="text/css">
        /*#service_id span.select2-container {
            z-index: 10050;
        }*/
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
                <h1>{{ ($performance) ? 'My Revenue By Centre Report' : 'Revenue By Centre Report' }}</h1>
            </div>
            <?php if ($performance == 'true') {
                $my_collection = 'true';
            } else {
                $my_collection = 'false';
            } ?>
            <div class="sn-buttons">
                @if( $medium_type == 'web')
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboardreport.revenue_by_centre',[$period , 'excel', $performance ]) }}" target="_blank">
                        <i class="fa fa-file-excel-o"></i><span>Excel</span>
                    </a>
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboardreport.revenue_by_centre',[$period,'pdf', $performance ]) }}" target="_blank">
                        <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                    </a>
                    <a class="btn sn-white-btn btn-default"
                       href="{{ route('admin.dashboardreport.revenue_by_centre',[$period,'print', $performance ]) }}" target="_blank">
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
                    <thead>
                    <th>Invoice No.</th>
                    <th>Centre</th>
                    <th>Service</th>
                    <th>Payment Date</th>
                    <th>Created by</th>
                    <th>Patient</th>
                    <th>Service Price</th>
                    <th>Discount Name</th>
                    <th>Discount Type</th>
                    <th>Discount Price</th>
                    <th>Subtotal</th>
                    <th>Tax Amount</th>
                    <th>Invoice Price/Total</th>
                    </thead>
                    <tbody>
                    @if(count($reportData))
                        <?php $grandserviceprice = 0; $grandtotalservice = 0; ?>
                        @foreach($reportData as $reportRow)
                            <tr>
                                <td style="text-align: center;">{{ $reportRow->id }}</td>
                                <td>{{ (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '' }}</td>
                                <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                                <td>{{ ($reportRow->created_at) ? \Carbon\Carbon::parse($reportRow->created_at, null)->format('M j, Y').' at '.\Carbon\Carbon::parse($reportRow->created_at, null)->format('h:i A') : '-' }}</td>
                                <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                                <td>{{ (array_key_exists($reportRow->patient_id, $filters['patients'])) ? $filters['patients'][$reportRow->patient_id]->name : '' }}</td>
                                <td style="text-align: right;">
                                    <?php
                                    $grandserviceprice += (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
                                    echo number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '',2);
                                    ?>
                                </td>
                                <td>{{$reportRow->discount_name?$reportRow->discount_name:''}}</td>
                                <td>{{$reportRow->discount_type?$reportRow->discount_type:''}}</td>
                                <td style="text-align: right;">{{$reportRow->discount_price?$reportRow->discount_price:''}}</td>
                                <td style="text-align: right;">{{number_format($reportRow->tax_exclusive_serviceprice,2)}}</td>
                                <td style="text-align: right;">{{number_format($reportRow->tax_price,2)}}</td>
                                <td style="text-align: right;">
                                    <?php
                                    $grandtotalservice += $reportRow->total_price;
                                    echo number_format($reportRow->total_price,2);
                                    ?>
                                </td>
                            </tr>
                        @endforeach
                        <tr style="background: #364150;color: #fff;">
                            <td style="text-align: center; color: #fff;">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;color: #fff;"><?php echo number_format($grandserviceprice,2);?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;color: #fff;"><?php echo number_format($grandtotalservice,2);?></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="12" align="center">No record round.</td>
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