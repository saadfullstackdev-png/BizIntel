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
            padding: 8px;
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
    </style>
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
                        <td>Consume Plan Revenue</td>
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