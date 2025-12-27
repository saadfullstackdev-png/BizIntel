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
            <h1>{{ 'Machine Wise Collection Report' }}</h1>
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
    <table class="table">
        <tr>
            <th>Center</th>
            <th>Region</th>
            <th>City</th>
            <th>Machine Type</th>
            <th>Client</th>
            <th>Cash Flow</th>
            <th>Cash In</th>
            <th>Refund/Cash Out</th>
            <th>Balance</th>
        </tr>
        @if(count($reportData))
            <?php $machinetotal_in_g = 0; $machinetotal_out_g = 0;  ?>
            @foreach($reportData as $reportlocation)
                <tr style="background-color: #dddddd;">
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
            <tr style="background-color: #364150; color: #fff;">
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