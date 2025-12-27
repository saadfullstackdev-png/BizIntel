@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Company Health Report' }}</h1>
        </div>
    </div>
</div>
<div class="invoice-pdf">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80" alt=""/>
        </div>
        <div class="print-time">
            <table class="dark-th-table table table-bordered">
                <tr>
                    <th width="25%">Duration</th>
                    <td>From {{ $start_date }} to {{ $end_date }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if (count($reportData))
        @if( count( $regions ))
            @foreach( $regions as $region )
                <table class="table">
                    <thead>
                    <tr style=" color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">{{ $account->name }}</td>
                    </tr>
                    <tr style=" color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">Health of the Company for Month
                            of {{ \Carbon\Carbon::parse($start_date)->format('M, Y') }}</td>
                    </tr>
                    <tr style=" color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">Region Wise Monthly Target
                            ({{ \Carbon\Carbon::parse($start_date)->format('M, Y') }})
                        </td>
                    </tr>
                    <tr style=" color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;"> {{ $region['region_name'] }}</td>
                    </tr>
                    <tr style=" color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">{{ $remaining_days }} Days
                            Remaining
                        </td>
                    </tr>
                    <tr>
                        <th>Sr#</th>
                        <th>Centre</th>
                        <th style="text-align: right;">Monthly Target</th>
                        <th style="text-align: right;">Month to Date</th>
                        <th style="text-align: right;">Revenue Still Outstanding to Hit Monthly Target</th>
                        <th style="text-align: right;">Revenue Required Per Day to Hit Target</th>
                        <th>Percentage</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($reportData))
                        @php $monthly_target_total = 0; $monthly_achived_total = 0; $count=1 ; $outstanding_revenue_total = 0; $per_day_required_total = 0 ;  @endphp
                        @foreach($reportData as $data)
                            @if ( $data['region_id'] === $region['region_id'])
                                <tr>
                                    <td style="text-align: center;">{{ $count++ }}</td>
                                    <td>{{ $data['name'] }}</td>
                                    <td style="text-align: right;">{{ number_format( $data['monthly_target'], 2) }}</td>
                                    <td style="text-align: right;">{{ number_format( $data['target_achieved'] , 2) }}</td>
                                    <td style="text-align: right;">{{ number_format( $data['revenue_outstanding'] , 2) }}</td>
                                    <td style="text-align: right;">{{ number_format( $data['perDayRequired'] , 2) }}</td>
                                    <td style="text-align: center;">{{ number_format( $data['Pecentage'] , 2) }}%</td>
                                </tr>
                                @php
                                    $monthly_target_total += $data['monthly_target'] ;
                                    $monthly_achived_total += $data['target_achieved'] ;
                                    $outstanding_revenue_total += $data['revenue_outstanding'] ;
                                    $per_day_required_total += $data['perDayRequired'] ;
                                @endphp
                            @endif
                        @endforeach
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;"> Total Target</td>
                            <td></td>
                            <td style="text-align: right;"> {{ number_format( $monthly_target_total , 2) }} </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;"> Total Month to Date</td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;"> {{number_format($monthly_achived_total,2)}} </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;"> Revenue Still Outstanding to Hit Monthly Target</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;">  {{ number_format( $outstanding_revenue_total, 2) }} </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;"> Avg. Revenue Required Per Day to Hit Target</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;"> {{ number_format( $per_day_required_total , 2) }} </td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;"> Total Month to Date Revenue %</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center;"> {{ number_format( ( $monthly_achived_total / $monthly_target_total ) * 100 , 2 ) }}
                                %
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
                <br>
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

</body>
</html>