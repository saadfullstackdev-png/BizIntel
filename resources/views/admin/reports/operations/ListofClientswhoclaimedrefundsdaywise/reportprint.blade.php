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
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
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
            background: #364150;
            color: #fff;
        }
        .tab-top{
            margin-top: 0;
        }

    </style>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'List of Clients who claimed refunds Day Base Against Plans Report
' }}</h1>
        </div>
    </div>
</div>
<div class="invoice-pdf">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80" alt=""/>
        </div>
        <div class="print-time">
            <table class="dark-th-table table table-bordered tab-top">
                <tr>
                    <th width="25%">Duration</th>
                    <td>For the month
                        of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>
    <table class="table">
        <tr class="shdoc-header">
            <th>ID</th>
            <th>Patient Name</th>
            <th>Centre</th>
            <th>Refund Note</th>
            <th>Amount</th>
            <th>Created At</th>
        </tr>
        @if(count($reportData))
            <?php $grefund = 0; ?>
            @foreach($reportData as $reportpackagedata)
                <tr>
                    <td>{{$reportpackagedata['name']}}</td>
                    <td style="text-align: right"><?php echo number_format($reportpackagedata['total_price'],2)?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $trefund = 0;?>
                @foreach($reportpackagedata['refunds'] as $reportRow )
                    <tr>
                        <td>{{$reportRow['patient_id']}}</td>
                        <td>{{$reportRow['patient']}}</td>
                        <td>{{$reportRow['location']}}</td>
                        <td>{{$reportRow['refund_note']}}</td>
                        <td style="text-align: right"><?php
                            $trefund+= $reportRow['cash_amount'];
                            echo number_format($reportRow['cash_amount'],2);
                            ?></td>
                        <td>{{($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y')  : '-'}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td>Refund Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right"><?php
                        $grefund+=$trefund;
                        echo number_format($trefund,2);
                        ?></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="color: #fff; background: #364150;">
                <td style="color: white">Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td style="color: white;text-align: right"><?php echo number_format($grefund,2)?></td>
                <td></td>
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

</body>
</html>