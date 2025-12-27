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
            <h1>{{'Plan Maturity Report'}}</h1>
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
        <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>Plan Name</th>
            <th>Is Refund</th>
            <th>Centre</th>
            <th>Total Price</th>
            <th>Advances</th>
            <th>Outstanding</th>
            <th>Use Balance</th>
            <th>Unused Balance</th>
        </tr>
        @if(count($reportData))
            <?php $gtotal = 0; $gadvances = 0; $goutstanding = 0; $guse = 0; $gunused = 0; ?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{ $reportRow['patient_id'] }}</td>
                    <td>{{ $reportRow['patient'] }}</td>
                    <td>{{ $reportRow['name'] }}</td>
                    <td>{{ $reportRow['is_refund'] }}</td>
                    <td>{{ $reportRow['location'] }}</td>
                    <td>{{ number_format($reportRow['total_price'],2) }}</td>
                    <td>{{ number_format($reportRow['advancebalance'],2) }}</td>
                    <td>{{ number_format($reportRow['outstandingbalance'],2) }}</td>
                    <td>{{ number_format($reportRow['usedbalance'],2) }}</td>
                    <td>{{ number_format($reportRow['unusedbalance'],2) }}</td>
                    <?php
                    $gtotal+=$reportRow['total_price'];
                    $gadvances+=$reportRow['advancebalance'];
                    $goutstanding+=$reportRow['outstandingbalance'];
                    $guse+=$reportRow['usedbalance'];
                    $gunused+=$reportRow['unusedbalance'];
                    ?>

                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;font-weight: bold">
                <td style="text-align: center;color: #fff;font-weight: bold">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="color: #fff;font-weight: bold">{{ number_format($gtotal,2) }}</td>
                <td style="color: #fff;font-weight: bold">{{ number_format($gadvances,2) }}</td>
                <td style="color: #fff;font-weight: bold">{{ number_format($goutstanding,2) }}</td>
                <td style="color: #fff;font-weight: bold">{{ number_format($guse,2) }}</td>
                <td style="color: #fff;font-weight: bold">{{ number_format($gunused,2) }}</td>
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