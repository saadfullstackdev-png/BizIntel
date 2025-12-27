@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{($performance == 'true')?'My Collection By Centre':'Collection By Centre Report'}}</h1>
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
        <thead>
        <th>Patient Name</th>
        <th>Transaction type</th>
        <th>Revenue Cash In</th>
        <th>Revenue Card In</th>
        <th>Revenue Bank/Wire In</th>
        <th>Refund/Out</th>
        <th>Cash In Hand</th>
        <th>Created At</th>
        </thead>
        <tbody>
        @if($report_data)
            @foreach($report_data as $reportlocation)
                @php
                    $total_cash_in = 0 ;
                    $total_card_in = 0 ;
                    $total_bank_in = 0 ;
                    $total_refund_out = 0 ;
                    $balance = 0 ;
                @endphp
                @if(!empty($reportlocation['revenue_data']))

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
                                {{number_format( ( $reportRow['revenue_cash_in'] > 0 ) ? $reportRow['revenue_cash_in'] : 0 )}}
                            </td>
                            <td>
                                {{number_format( ( $reportRow['revenue_card_in'] > 0 ) ? $reportRow['revenue_card_in'] : 0 )}}
                            </td>
                            <td>
                                {{ number_format( ( $reportRow['revenue_bank_in'] > 0 ) ? $reportRow['revenue_bank_in'] : 0 ) }}
                            </td>
                            <td>
                                {{number_format( ( $reportRow['refund_out'] > 0 ) ? $reportRow['refund_out'] : 0 )}}
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
                    <tr style="background-color:#364150;color: #fff;">
                        <td>{{$reportlocation['name']}}</td>
                        <td>Total</td>
                        <td>{{ number_format( $total_cash_in ,2 ) }}</td>
                        <td>{{ number_format( $total_card_in , 2 ) }}</td>
                        <td>{{ number_format( $total_bank_in , 2 ) }}</td>
                        <td>{{ number_format( $total_refund_out , 2 ) }}</td>
                        <td>{{ number_format( $balance , 2 ) }}</td>
                        <td></td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
</div>

</body>
</html>