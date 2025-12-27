@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
    @if($request->get('medium_type') == 'pdf')
        @include('partials.pdf_head')
    @else
        @include('partials.head')
    @endif
    <style type="text/css">
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
            .shdoc-header{
                background: #364150;
                color: #fff;
            }
        }
    </style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Appointments Summary By Service Report'  }}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('excel');">
                <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('print');">
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
            <div class="table-wrapper" id="topscroll">
            <table class="table">
                <thead>
                    <tr>
                        <th width="10%">Region</th>
                        <th width="25%">Centre</th>
                        <th>Service</th>
                        <th style="text-align:right; padding-right:20px;">Total Appointments</th>
                    </tr>
                </thead>
                <tbody>
                @if(count($reportData))
                    @php($grand_total = 0)
                    @foreach($reportData as $region)
                        @if(count($region['centres']))
                            <tr>
                                <th colspan="4">{{ $region['name'] }}</th>
                            </tr>
                            @php($region_appointments = 0)
                            @foreach($region['centres'] as $centre)
                                <tr>
                                    <th></th>
                                    <th colspan="3">{{ $centre['name'] }}</th>
                                </tr>
                                @php($centre_appointments = 0)
                                @if(count($centre['services']))
                                    @foreach($centre['services'] as $service)
                                        @php($centre_appointments = $centre_appointments + $service['total_appointments'])
                                        <tr>
                                            <td colspan="2"></td>
                                            <td>{{ $service['name'] }}</td>
                                            <td style="text-align:right; padding-right:20px;">{{ number_format($service['total_appointments']) }}</td>
                                        </tr>
                                    @endforeach
                                    @php($region_appointments = $region_appointments + $centre_appointments)
                                    <tr>
                                        <th colspan="3" style="text-align: right;">Total for {{ $centre['name'] }}</th>
                                        <th style="text-align:right; padding-right:20px;">{{ number_format($centre_appointments) }}</th>
                                    </tr>
                                @endif
                            @endforeach
                            @php($grand_total = $grand_total + $region_appointments)
                            <tr style="background-color: #37abdc; color: #fff;">
                                <th colspan="3" style="text-align: right;">Total for {{ $region['name'] }}</th>
                                <th style="text-align:right; padding-right:20px;">{{ number_format($region_appointments) }}</th>
                            </tr>
                        @endif
                    @endforeach
                    <tr style="background: #364150;color: #fff;">
                        <th colspan="3" style="text-align: right;color: #fff;">Total for All Regions</th>
                        <th style="text-align:right; padding-right:20px;">{{ number_format($grand_total) }}</th>
                    </tr>
                @else
                    @if($message)
                        <tr>
                            <td colspan="12" align="center">{{$message}}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="12" align="center">No record round.</td>
                        </tr>
                    @endif()
                @endif
                </tbody>
            </table>
        </div>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>