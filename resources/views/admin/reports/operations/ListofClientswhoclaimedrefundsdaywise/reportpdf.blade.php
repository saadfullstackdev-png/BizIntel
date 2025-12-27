@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf{
            width: 100%;
        }
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
        .table{
            width: 100%;
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
        table.table tr td{
            padding: 12px 5px;
        }
        table.table tr:first-child{
            background-color: #fff;
        }
        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
        .shdoc-header{
            background: #364150;
            color: #fff;
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
            <td style="padding-left: 450px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>List of Clients who claimed refunds Day Base Against Plans Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
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
            <tr class="shdoc-header">
                <td style="color: white; font-weight: bold">Grand Total</td>
                <td style="color: white;font-weight: bold"></td>
                <td style="color: white; font-weight: bold"></td>
                <td style="color: white; font-weight: bold"></td>
                <td style="color: white; font-weight: bold"><?php echo number_format($grefund,2)?></td>
                <td style="color: white; font-weight: bold"></td>
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