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
            <h1>{{ 'List Of Outstanding As Of Today For Non Plan Report'  }}</h1>
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
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Email</th>
            <th>Scdeuled</th>
            <th>Doctor</th>
            <th>City</th>
            <th>Center</th>
            <th>Total Price</th>
            <th>Advance</th>
            <th>Outstanding</th>
            <th>Used Balance</th>
            <th>Unused Balance</th>
        </tr>
        @if(count($reportData))
            <?php $gtotal = 0; $gadvances = 0; $goutstanding = 0; $guse = 0; $gunused = 0; ?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{$reportRow['patient_id']}}</td>
                    <td>{{$reportRow['patient_name']}}</td>
                    <td>{{$reportRow['email']}}</td>
                    <td>{{$reportRow['schedule']}}</td>
                    <td>{{$reportRow['doctor']}}</td>
                    <td>{{$reportRow['city']}}</td>
                    <td>{{$reportRow['location']}}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['total_price'],2) }}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['advancebalance'],2) }}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['outstandingbalance'],2) }}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['usedbalance'],2) }}</td>
                    <td  style="text-align: right;">{{ number_format($reportRow['unusedbalance'],2) }}</td>
                    <?php
                    $gtotal+=$reportRow['total_price'];
                    $gadvances+=$reportRow['advancebalance'];
                    $goutstanding+=$reportRow['outstandingbalance'];
                    $guse+=$reportRow['usedbalance'];
                    $gunused+=$reportRow['unusedbalance'];
                    ?>

                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;">
                <td style="text-align: center;color: #fff;">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right; color: #fff;">{{ number_format($gtotal,2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($gadvances,2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($goutstanding,2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($guse,2) }}</td>
                <td style="text-align: right; color: #fff;">{{ number_format($gunused,2) }}</td>
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