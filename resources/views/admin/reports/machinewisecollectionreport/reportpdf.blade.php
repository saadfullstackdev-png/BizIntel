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
            color: #fff;
            background-color: #364150;
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
                        <td>Machine Wise Collection Report</td>
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
            <th>Machine Type</th>
            <th>Client</th>
            <th>Cash Flow</th>
            <th>Cash In</th>
            <th>Refund</th>
            <th>Balance</th>
        </tr>
        @if(count($reportData))
            <?php $machinetotal_in_g = 0; $machinetotal_out_g = 0;  ?>
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
                </tr>
                <?php $machinetotal_in_t = 0; $machinetotal_out_t = 0;  ?>
                @foreach($reportlocation['machine_types'] as $reportmachine )
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">{{ $reportmachine['name'] }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php $machinetotal_in = 0; $machinetotal_out = 0;  ?>
                    @foreach($reportmachine['transaction'] as $paymentrecord)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>{{$paymentrecord['name']}}</td>
                            <td>{{$paymentrecord['flow']}}</td>
                            <td>{{$paymentrecord['amount_in']?number_format($paymentrecord['amount_in'],2):''}}</td>
                            <td>{{$paymentrecord['amount_out']?number_format($paymentrecord['amount_out'],2):''}}</td>
                            <td></td>
                            @php
                                $machinetotal_in+=$paymentrecord['amount_in']?$paymentrecord['amount_in']:0;
                                $machinetotal_out+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                                $machinetotal_in_t+=$paymentrecord['amount_in']?$paymentrecord['amount_in']:0;
                                $machinetotal_out_t+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                                $machinetotal_in_g+=$paymentrecord['amount_in']?$paymentrecord['amount_in']:0;
                                $machinetotal_out_g+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                            @endphp
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">Total</td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold">{{number_format($machinetotal_in,2)}}</td>
                        <td style="font-weight: bold">{{number_format($machinetotal_out,2)}}</td>
                        <td style="font-weight: bold">{{number_format($machinetotal_in-$machinetotal_out,2)}}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #35a1d4; color: #fff;">
                    <td style="font-weight: bold">Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold">{{number_format($machinetotal_in_t,2) }}</td>
                    <td style="font-weight: bold">{{number_format($machinetotal_out_t,2) }}</td>
                    <td style="font-weight: bold">{{number_format($machinetotal_in_t-$machinetotal_out_t,2)}}</td>
                </tr>
            @endforeach
            <tr  style="color: #fff; background-color: #364150;">
                <td colspan="3" style="font-weight: bold">Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-weight: bold">{{number_format($machinetotal_in_g,2) }}</td>
                <td style="font-weight: bold">{{number_format($machinetotal_out_g,2) }}</td>
                <td style="font-weight: bold">{{number_format($machinetotal_in_g-$machinetotal_out_g,2)}}</td>
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