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

        table.table tr td {
            padding: 12px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
        .shdoc-header{
            background: #364150; color: #fff;
        }
    </style>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css">
</head>
<body>
<div class="invoice-pdf">
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}"
                                 class="img-responsive" alt=""/>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding-left: 450px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Customer Treatment Package Ledger report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table">
        <tr style="background: #364150; color: #fff;">
            <th>ID</th>
            <th>Patient Name</th>
            <th>Transaction Type</th>
            <th>Cash In</th>
            <th>Cash Out</th>
            <th>Balance</th>
            <th>Created At</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $reportpackage_advances)
                <tr class="sh-docblue">
                    <td>{{ $reportpackage_advances['name'] }}</td>
                    <td>{{ $reportpackage_advances['patient'] }}</td>
                    <td> {{ $reportpackage_advances['location'] }}</td>
                    <td> {{ number_format($reportpackage_advances['total_price']) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($reportpackage_advances['children'] as $reportRow )
                    <tr>
                        <td>{{$reportRow['patient_id']}}</td>
                        <td>{{$reportRow['patient']}}</td>
                        <td>{{$reportRow['transtype']}}</td>
                        <td>{{$reportRow['cash_in']}}</td>
                        <td>{{$reportRow['cash_out']}}</td>
                        <td>{{$reportRow['balance']}}</td>
                        <td>{{\Carbon\Carbon::parse($reportRow['created_at'])->format('F j,Y h:i A')}}</td>
                    </tr>
                @endforeach
            @endforeach
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