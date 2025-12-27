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
            <h1>{{ 'Summarized Data of Discounts given to the customer Report'  }}</h1>
        </div>
    </div>
</div>

<div class="panel-body sn-table-body">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
        </div>
        <div class="print-time">
            <table class="dark-th-table table table-bordered">
                <tr>
                    <th width="25%">Duration</th>
                    <td>From {{ $start_date }} to {{ $end_date }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table">
        <tr style="background: #364150; color: #fff;">
            <th>ID</th>
            <th>Package Name</th>
            <th>Patient Name</th>
            <th>Location</th>
            <th>Is Refund</th>
            <th>Orignal Price</th>
            <th>Discount Price</th>
            <th>Tax Amount</th>

        </tr>
        @if(count($reportData))
            <?php $gorignaltotal = 0; $gdiscounttotal = 0; $grandtaxtotal = 0;?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{$reportRow['patient_id']}}</td>
                    <td>{{$reportRow['name']}}</td>
                    <td>{{$reportRow['patient']}}</td>
                    <td>{{$reportRow['location']}}</td>
                    <td>{{$reportRow['is_refund']}}</td>
                    <td style="text-align: right;">{{ number_format($reportRow['orignal_price'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($reportRow['discount_price'], 2) }}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['tax_amt'],2) }}</td>
                <?php
                    $gorignaltotal += $reportRow['orignal_price'];
                    $gdiscounttotal += $reportRow['discount_price'];
                    $grandtaxtotal += $reportRow['tax_amt'];
                    ?>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;">
                <td style="text-align: center; color: #fff;">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right; color: #fff;">{{ number_format($gorignaltotal, 2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($gdiscounttotal, 2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($grandtaxtotal, 2) }}</td>
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