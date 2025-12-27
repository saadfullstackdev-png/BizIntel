@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/></head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Machine Wise Invoice Revenue Report' }}</h1>
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
                @foreach($reportlocation['machine'] as $reportmachine)
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
                            <?php $machinetotal += $paymentrecord['net_amount']; ?>
                            <?php $centotal += $paymentrecord['net_amount']; ?>
                            <?php $grantotal += $paymentrecord['net_amount']; ?>
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
                <tr class="sh-docblue">
                    <td style="color: #fff">Total</td>
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
                    <td style="color: #fff">{{number_format($centotal) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background-color: #364150; color: #fff;">
                <td colspan="3" style="font-weight: bold">Grand Total</td>
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