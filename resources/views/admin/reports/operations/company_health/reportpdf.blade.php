@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .date {
            text-align: right;
        }

        .logo {
            width: 200px;
            text-align: left;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            margin-top: 30px;
        }

        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td, th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        .table tr:first-child {
            background-color: #fff;
        }

        table.table tr td {
            padding: 12px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table > tbody > tr > td, .table > tbody > tr > th, .table > tfoot > tr > td, .table > tfoot > tr > th, .table > thead > tr > td, .table > thead > tr > th {
            padding: 8px;
            line-height: 1.42857;
            vertical-align: top;
            border-top: 1px solid #e7ecf1;
        }
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" class="img-responsive"
                                 alt=""/>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="float:right">
                <table align="right">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Company Health Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td><strong>From {{ $start_date }} to {{ $end_date }}</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if (count($reportData))
        @if( count( $regions ))
            @foreach( $regions as $region )
                <table class="table">
                    <tr style="color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">{{ $account->name }}</td>
                    </tr>
                    <tr style="color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">Health of the Company for Month
                            of {{ \Carbon\Carbon::parse($start_date)->format('M, Y') }}</td>
                    </tr>
                    <tr style="color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">Region Wise Monthly Target
                            ({{ \Carbon\Carbon::parse($start_date)->format('M, Y') }})
                        </td>
                    </tr>
                    <tr style="color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;"> {{ $region['region_name'] }}</td>
                    </tr>
                    <tr style="color: #fff; background-color: #364150;">
                        <td colspan="7" style="text-align: center;font-weight: bold;">{{ $remaining_days }} Days
                            Remaining
                        </td>
                    </tr>
                    <tr style="color: #fff; background-color: #364150;">
                        <th>Sr#</th>
                        <th>Centre</th>
                        <th>Monthly Target</th>
                        <th>Month to Date</th>
                        <th>Revenue Still Outstanding to Hit Monthly Target</th>
                        <th>Revenue Required Per Day to Hit Target</th>
                        <th>Percentage</th>
                    </tr>

                    @if(count($reportData))
                        <?php $monthly_target_total = 0; $monthly_achived_total = 0; $count = 1; $outstanding_revenue_total = 0; $per_day_required_total = 0; ?>
                        @foreach( $reportData as $reportsingle )
                            @if ( $reportsingle['region_id'] === $region['region_id'])
                                <tr>
                                    <td>{{$count++}}</td>
                                    <td>{{$reportsingle['name']}}</td>
                                    <td style="text-align: right;">{{ number_format( $reportsingle['monthly_target'] , 2) }}</td>
                                    <td style="text-align: right;"> {{ number_format( $reportsingle['target_achieved'] , 2) }}</td>
                                    <td style="text-align: right;">{{ number_format( $reportsingle['revenue_outstanding'] , 2)}}</td>
                                    <td style="text-align: right;">  {{ number_format( $reportsingle['perDayRequired'] , 2) }}</td>
                                    <td style="text-align: center;">{{ number_format( $reportsingle['Pecentage'] , 2) }}
                                        %
                                    </td>
                                    @php
                                        $monthly_target_total+=$reportsingle['monthly_target'];
                                        $monthly_achived_total+=$reportsingle['target_achieved'];
                                        $outstanding_revenue_total += $reportsingle['revenue_outstanding'];
                                        $per_day_required_total += $reportsingle['perDayRequired'];
                                    @endphp
                                </tr>
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
                            <td style="text-align: center;">Total Month to Date</td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;">{{number_format($monthly_achived_total,2)}} </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;">Revenue Still Outstanding to Hit Monthly Target</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;">{{ number_format( $outstanding_revenue_total, 2) }} </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;">Avg. Revenue Required Per Day to Hit Target</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;"> {{ number_format( $per_day_required_total , 2) }} </td>
                            <td></td>
                        </tr>
                        <tr style="background-color:#3aaddc;color: #fff; text-align: right;">
                            <td style="text-align: center;">Total Month to Date Revenue %</td>
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