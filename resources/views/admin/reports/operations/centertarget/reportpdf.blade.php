<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />

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

        table.table tr td{
            padding: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }
        .shdoc-header{
            background: #364150;
            color: #fff;
        }
        .sh-bold{
            font-weight: 700;
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
                        <td >
                            <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" class="img-responsive" alt=""/>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding-left: 100px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Monthly Target Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table table-default">
        <tr class="shdoc-header">
            <th>Sr#</th>
            <th>Centre</th>
            <th>Region</th>
            <th>City</th>
            <th>Monthly Target</th>
            <th>Monthly Achieved</th>
            <th>Percentage</th>
        </tr>
        @if(count($reportData))
            <?php $monthly_target_total = 0; $monthly_achived_total = 0; $count=1?>
            @foreach($reportData as $reportsingle)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$reportsingle['name']}}</td>
                    <td>{{$reportsingle['region']}}</td>
                    <td>{{$reportsingle['city']}}</td>
                    <td>{{number_format($reportsingle['monthly_target'],2)}}</td>
                    <td>{{number_format($reportsingle['target_achieved'],2)}}</td>
                    <td>{{number_format($reportsingle['Pecentage'],2)}}%</td>
                    @php
                        $monthly_target_total+=$reportsingle['monthly_target'];
                        $monthly_achived_total+=$reportsingle['target_achieved'];
                    @endphp
                </tr>
            @endforeach
            <tr style="background-color:#3aaddc;color: #fff;">
                <td class="sh-bold">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="sh-bold">{{number_format($monthly_target_total,2)}}</td>
                <td class="sh-bold">{{number_format($monthly_achived_total,2)}}</td>
                <td class="sh-bold">{{number_format(($monthly_achived_total/$monthly_target_total)*100,2)}}%</td>
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
    </table>
</div>
</div>

</body>
</html>