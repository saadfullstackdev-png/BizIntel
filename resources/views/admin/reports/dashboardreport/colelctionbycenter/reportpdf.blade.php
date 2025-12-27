@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <title>Collection By Centre Report</title>
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
        table.table tr td{
            padding: 12px;
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
                        <td>{{($performance == 'true')?'My Collection By Centre':'Collection By Centre Report'}}</td>
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
        <tr style="background-color:#364150;color: #fff;">
            <th>Patient Name</th>
            <th>Transaction type</th>
            <th>Revenue Cash In</th>
            <th>Revenue Card In</th>
            <th>Revenue Bank/Wire In</th>
            <th>Refund/Out</th>
            <th>Cash In Hand</th>
            <th>Created At</th>
        </tr>
        @if($report_data)
            @foreach($report_data as $reportlocation)

                @php
                    $total_cash_in = 0 ;
                    $total_card_in = 0 ;
                    $total_bank_in = 0;
                    $total_refund_out = 0 ;
                    $balance = 0 ;
                @endphp

                <tr>
                    <td>{{$reportlocation['name']}}</td>
                    <td>{{$reportlocation['city']}}</td>
                    <td>{{$reportlocation['region']}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($reportlocation['revenue_data'] as $reportRow)
                    <tr>
                        <td>{{$reportRow['patient']}}</td>
                        <td>{{$reportRow['transtype']}}</td>
                        <td>
                            {{ number_format( ( $reportRow['revenue_cash_in'] > 0 ) ? $reportRow['revenue_cash_in'] : 0 ) }}
                        </td>
                        <td>
                            {{ number_format( ( $reportRow['revenue_card_in'] > 0 ) ? $reportRow['revenue_card_in'] : 0 ) }}
                        </td>
                        <td>
                            {{ number_format( ( $reportRow['revenue_bank_in'] > 0 ) ? $reportRow['revenue_bank_in'] : 0 ) }}
                        </td>
                        <td>
                            {{ number_format( ( $reportRow['refund_out'] > 0 ) ? $reportRow['refund_out'] : 0 ) }}
                        </td>
                        <td></td>
                        <td>{{$reportRow['created_at']}}</td>
                    </tr>
                    @php
                        $total_cash_in += $reportRow['revenue_cash_in']>0?$reportRow['revenue_cash_in']:0 ;
                        $total_card_in += $reportRow['revenue_card_in']>0?$reportRow['revenue_card_in']:0;
                        $total_bank_in += $reportRow['revenue_bank_in']>0?$reportRow['revenue_bank_in']:0;
                        $total_refund_out += $reportRow['refund_out']>0?$reportRow['refund_out']:0;
                    @endphp
                @endforeach
                @php
                    $balance = $total_cash_in + $total_card_in + $total_bank_in - $total_refund_out ;
                @endphp
                <tr style="background-color:#364150;color: #fff;font-weight:bold">
                    <td>{{$reportlocation['name']}}</td>
                    <td>Total</td>
                    <td>{{ number_format( $total_cash_in ,2 ) }}</td>
                    <td>{{ number_format( $total_card_in , 2 ) }}</td>
                    <td>{{ number_format( $total_bank_in , 2 ) }}</td>
                    <td>{{ number_format( $total_refund_out , 2 ) }}</td>
                    <td>{{ number_format( $balance , 2 ) }}</td>
                    <td></td>
                </tr>

            @endforeach
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
        @endif
    </table>
</div>
</div>

</body>
</html>