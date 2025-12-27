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
                        <td>Pabau Record Revenue Report</td>
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
    <table class="table">
        <tr style=" color: #fff;background-color: #364150;">
            <th>Center</th>
            <th>Region</th>
            <th>City</th>
            <th>Client</th>
{{--            <th>Phone</th>--}}
            <th>Invoice No.</th>
            <th>Issue Date</th>
            <th>Total Amount</th>
            <th>Paid Amount</th>
            <th>Outstanding Amount</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        @if(count($reportData))
            <?php $grantotal = 0; ?>
            @foreach($reportData as $reportlocation)
                <tr style="background-color: #35a1d4; color: #fff;">
                    <td style="font-weight: bold"><?php echo $reportlocation['name']; ?></td>
                    <td style="font-weight: bold"><?php echo $reportlocation['region']; ?></td>
                    <td style="font-weight: bold"><?php echo $reportlocation['city']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
{{--                    <td></td>--}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $centotal = 0; ?>
                @foreach($reportlocation['pabau_rocord'] as $reportuser )
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $reportuser['name'] }}</td>
{{--                        <td>{{ $reportuser['phone'] }}</td>--}}
                        <td>{{ $reportuser['invoice_no'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($reportuser['issue_date'])->format('M j, Y H:i A') }}</td>
                        <td>{{ number_format($reportuser['total_amount']) }}</td>
                        <td>{{ number_format($reportuser['paid_amount'])}}</td>
                        <td>{{ number_format($reportuser['outstanding_amount']) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php $sumtotal = 0; ?>
                    @foreach($reportuser['pabau_record_payment'] as $paymentrecord)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
{{--                            <td></td>--}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($paymentrecord['amount'])}}</td>
                            <td>{{ \Carbon\Carbon::parse($paymentrecord['Date'])->format('M j, Y H:i A') }}</td>
                            <?php $sumtotal+=$paymentrecord['amount']; ?>
                            <?php $centotal+=$paymentrecord['amount']; ?>
                            <?php $grantotal+=$paymentrecord['amount']; ?>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
{{--                        <td></td>--}}
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">Total</td>
                        <td style="font-weight: bold">{{number_format($sumtotal) }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style="background-color: #35a1d4; color: #fff;">
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">Total</td>
                    <td></td>
                    <td></td>
{{--                    <td></td>--}}
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{number_format($centotal) }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr  style=" color: #fff;background-color: #364150;">
                <td colspan="3" align="right" style="font-weight: bold">Grand Total</td>
                <td></td>
                <td></td>
{{--                <td></td>--}}
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-weight: bold">{{number_format($grantotal) }}</td>
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
</div>

</body>
</html>