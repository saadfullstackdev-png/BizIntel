@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Consume Plan Revenue' }}</h1>
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
            <th>Plan ID</th>
            <th>Service</th>
            <th>Center</th>
            <th>Service Price</th>
            <th>Discount Name</th>
            <th>Discount Type</th>
            <th>Discount Amount</th>
            <th>Amount</th>
            <th>Tax</th>
            <th>Tax Value</th>
            <th>Total Amount</th>
            <th>Is Exclusive</th>
        </tr>
        @if(count($reportData))
            @php $amount_t = 0; $tax_price_t = 0; $total_amount_t = 0; @endphp
            @foreach($reportData as $reportRow)
                    <tr>
                        <td>{{$reportRow['plan_id']}}</td>
                        <td>{{$reportRow['service']}}</td>
                        <td>{{$reportRow['location']}}</td>
                        <td>{{number_format($reportRow['service_price'])}}</td>
                        <td>{{$reportRow['disocunt_name']?$reportRow['disocunt_name']:'-'}}</td>
                        <td>{{$reportRow['discount_type']?$reportRow['discount_type']:'-'}}</td>
                        <td>{{$reportRow['discount_amount']?number_format($reportRow['discount_amount']):'-'}}</td>
                        <td style="text-align: right">{{number_format($reportRow['amount'])}}</td>
                        <td>{{$reportRow['tax'].'%'}}</td>
                        <td style="text-align: right">{{$reportRow['is_exclusive'] == 1?number_format($reportRow['tax_value']):number_format($reportRow['tax_amount']-$reportRow['amount'])}}</td>
                        <td style="text-align: right">{{number_format($reportRow['tax_amount'])}}</td>
                        <td>{{$reportRow['is_exclusive']==1?'Yes':'No'}}</td>
                        @php
                            $amount_t += $reportRow['amount'];
                            $tax_price_t += $reportRow['is_exclusive'] == 1?$reportRow['tax_value']:$reportRow['tax_amount']-$reportRow['amount'];
                            $total_amount_t += $reportRow['tax_amount'];
                        @endphp
                    </tr>
            @endforeach

            <tr style="background: #364150;color: #fff;">
                <td style="text-align: center; color: #fff;">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;color: #fff;">{{ number_format( $amount_t) }}</td>
                <td></td>
                <td style="text-align: right;color: #fff;">{{ number_format( $tax_price_t) }}</td>
                <td style="text-align: right;color: #fff;"> {{ number_format( $total_amount_t) }} </td>
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