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
            .sh-bold{
                font-weight: 700;
                color: #fff;
            }
        }
    </style>
@endif

<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Company Health Report' }}</h1>
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
                @if (count($regions))
                    @if (count($reportData))
                        @foreach( $regions as $region )
                            <table class="table">
                                <thead>
                                <th colspan="7" style="text-align: center;">{{ $account->name }}</th>
                                </thead>
                                <thead>
                                <th colspan="7" style="text-align: center;">Health of the Company for Month of {{ \Carbon\Carbon::parse($start_date)->format('M, Y') }}</th>
                                </thead>
                                <thead>
                                <th colspan="7" style="text-align: center;">Region Wise Monthly Target ({{ \Carbon\Carbon::parse($start_date)->format('M, Y') }})</th>
                                </thead>
                                <thead>
                                <th colspan="7" style="text-align: center;">{{ $region['region_name'] }}</th>
                                </thead>
                                <thead>
                                <th colspan="7" style="text-align: center;">{{ $remaining_days }} Days Remaining</th>
                                </thead>
                                <thead>
                                <th>Sr#</th>
                                <th>Centre</th>
                                <th>Monthly Target</th>
                                <th>Month to Date</th>
                                <th>Revenue Still Outstanding to Hit Monthly Target</th>
                                <th>Revenue Required Per Day to Hit Target</th>
                                <th>Percentage</th>
                                </thead>
                                <tbody>
                                @if(count($reportData))
                                    <?php $monthly_target_total = 0; $monthly_achived_total = 0; $count=1 ; $outstanding_revenue_total = 0; $per_day_required_total = 0 ; ?>
                                    @foreach($reportData as $reportsingle)
                                        @if ( $reportsingle['region_id'] === $region['region_id'])
                                            <tr>
                                                <td>{{$count++}}</td>
                                                <td>{{$reportsingle['name']}}</td>
                                                <td style="text-align: right;">{{ number_format( $reportsingle['monthly_target'] , 2) }}</td>
                                                <td style="text-align: right;"> {{ number_format( $reportsingle['target_achieved'] , 2) }}</td>
                                                <td style="text-align: right;">{{ number_format( $reportsingle['revenue_outstanding'] , 2)}}</td>
                                                <td style="text-align: right;">  {{ number_format( $reportsingle['perDayRequired'] , 2) }}</td>
                                                <td style="text-align: right;">{{ number_format( $reportsingle['Pecentage'] , 2) }}%</td>
                                                @php
                                                    $monthly_target_total+=$reportsingle['monthly_target'];
                                                    $monthly_achived_total+=$reportsingle['target_achieved'];
                                                    $outstanding_revenue_total += $reportsingle['revenue_outstanding'];
                                                    $per_day_required_total += $reportsingle['perDayRequired'];
                                                @endphp
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr style="background-color:#3aaddc;color: #fff;">
                                        <td style="color: #fff;font-weight: 600">Total Target</td>
                                        <td></td>
                                        <td style="color: #fff;font-weight: 600; text-align: right;">{{number_format($monthly_target_total,2)}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr style="background-color:#3aaddc;color: #fff;">
                                        <td style="color: #fff;font-weight: 600">Total Month to Date</td>
                                        <td></td>
                                        <td></td>
                                        <td style="color: #fff;font-weight: 600; text-align: right;">{{number_format($monthly_achived_total,2)}}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr style="background-color:#3aaddc;color: #fff;">
                                        <td style="color: #fff;font-weight: 600">Revenue Still Outstanding to Hit Monthly Target</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="color: #fff;font-weight: 600; text-align: right;"> {{ number_format( $outstanding_revenue_total, 2) }}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr style="background-color:#3aaddc;color: #fff;">
                                        <td style="color: #fff;font-weight: 600">Avg. Revenue Required Per Day to Hit Target</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="color:#fff ; font-weight: 600 ;text-align: right;"> {{ number_format( $per_day_required_total , 2) }}</td>
                                        <td></td>
                                    </tr>
                                    <tr style="background-color:#3aaddc;color: #fff;">
                                        <td style="color: #fff;font-weight: 600">Total Month to Date Revenue %</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="color: #fff;font-weight: 600; text-align: right;">{{ number_format( ( $monthly_achived_total / $monthly_target_total ) * 100 , 2 ) }} % </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="12" align="center">No record round.</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        @endforeach
                    @endif
                @else
                    <table>
                        @if($message)
                            <tr>
                                <td colspan="12" align="center">{{$message}}</td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="12" align="center">No record round.</td>
                            </tr>
                        @endif()
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>