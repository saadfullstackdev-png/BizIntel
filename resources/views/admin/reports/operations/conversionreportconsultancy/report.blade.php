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
            <h1>{{ 'Conversion Report For Consultancy'  }}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('print');">
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
                    <th>Region</th>
                    <th>Centre</th>
                    <th>Booked GC's</th>
                    <th>Arrived GC's</th>
                    <th>Arrival Ratio(%)</th>
                    <th>Converted GC's</th>
                    <th>Converted Ratio(%)</th>
                    </thead>
                    <tbody>
                    @if(count($reportData))
                        @php $g_total = 0; $g_arrived = 0; $g_converted = 0;@endphp
                        @foreach($reportData as $region)
                            <tr>
                                <th colspan="7">{{ $region['name'] }}</th>
                            </tr>
                            @php $t_total = 0; $t_arrived = 0; $t_converted = 0; @endphp
                            @foreach($region['location'] as $centre)
                                <tr>
                                    <th></th>
                                    <td>{{$centre['location_name']}}</td>
                                    <td>{{$centre['booked']}}</td>
                                    <td>{{$centre['arrived']}}</td>
                                    <td>{{number_format($centre['arrival_ratio'],2)}}%</td>
                                    <td>{{$centre['converted']}}</td>
                                    <td>{{number_format($centre['conversion_ratio'],2)}}%</td>
                                </tr>
                                @php
                                    $t_total += $centre['booked'];
                                    $t_arrived += $centre['arrived'];
                                    $t_converted += $centre['converted'];
                                    $g_total += $centre['booked'];
                                    $g_arrived += $centre['arrived'];
                                    $g_converted += $centre['converted'];
                                @endphp
                            @endforeach
                            <tr style="background-color: #37abdc; color: #fff">
                                <th>Total</th>
                                <th></th>
                                <th>{{number_format($t_total)}}</th>
                                <th>{{number_format($t_arrived)}}</th>
                                <th>{{$t_total > 0?number_format(($t_arrived/$t_total)*100,2):0}}%</th>
                                <th>{{number_format($t_converted)}}</th>
                                <th>{{$t_arrived > 0?number_format(($t_converted/$t_arrived)*100,2):0}}%</th>
                            </tr>
                        @endforeach
                        <tr style="background: #364150;color: #fff;">
                            <th>Grand Total</th>
                            <th></th>
                            <th>{{number_format($g_total)}}</th>
                            <th>{{number_format($g_arrived)}}</th>
                            <th>{{$g_total > 0?number_format(($g_arrived/$g_total)*100,2):0}}%</th>
                            <th>{{number_format($g_converted)}}</th>
                            <th>{{$g_arrived?number_format(($g_converted/$g_arrived)*100,2):0}}%</th>
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