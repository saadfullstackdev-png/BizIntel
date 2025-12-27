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
                        <td>Machine Wise Invoice Revenue Report</td>
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
        <tr style="background-color: #364150;color:#fff">
            <th>Center</th>
            <th>Region</th>
            <th>City</th>
            <th>Machine</th>
            <th>Client</th>
            <th>Service Price</th>
            <th>Discount Name</th>
            <th>Discount Type</th>
            <th>Discount Price</th>
            <th>Amount</th>
            <th>Tax Value</th>
            <th>Net Amount</th>
            <th>Created At</th>
            <th>Is Exclusive</th>
        </tr>
        @if(count($reportData))
            <?php $grantotal = 0; ?>
            @foreach($reportData as $reportlocation)
                <tr>
                    <td style="font-weight: bold"><?php echo $reportlocation['name']; ?></td>
                    <td style="font-weight: bold"><?php echo $reportlocation['region']; ?></td>
                    <td style="font-weight: bold"><?php echo $reportlocation['city']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $centotal = 0; ?>
                @foreach($reportlocation['machine'] as $reportmachine )
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $reportmachine['name'] }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php $machinetotal = 0; ?>
                    @foreach($reportmachine['machine_array'] as $paymentrecord)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{$paymentrecord['client']}}</td>
                            <td>{{number_format($paymentrecord['service_price'],2)}}</td>
                            <td>{{$paymentrecord['discount_name']}}</td>
                            <td>{{$paymentrecord['discount_type']}}</td>
                            <td>{{number_format($paymentrecord['discount_price'],2)}}</td>
                            <td>{{number_format($paymentrecord['amount'],2)}}</td>
                            <td>{{number_format($paymentrecord['tax_value'],2)}}</td>
                            <td>{{number_format($paymentrecord['net_amount'],2)}}</td>
                            <td>{{ \Carbon\Carbon::parse($paymentrecord['created_at'])->format('M j, Y H:i A') }}</td>
                            <td>{{ $paymentrecord['is_exclusive']?'Yes':'NO' }}</td>
                            <?php $machinetotal+=$paymentrecord['net_amount']; ?>
                            <?php $centotal+=$paymentrecord['net_amount']; ?>
                            <?php $grantotal+=$paymentrecord['net_amount']; ?>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">{{number_format($machinetotal) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style="background-color: #35a1d4; color: #fff;">
                    <td style="font-weight: bold">Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{number_format($centotal) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background-color: #364150;color:#fff">
                <td colspan="3"  style="font-weight: bold">Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-weight: bold">{{number_format($grantotal) }}</td>
                <td></td>
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