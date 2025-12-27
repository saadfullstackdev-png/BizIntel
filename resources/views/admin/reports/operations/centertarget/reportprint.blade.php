<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Center Target Report' }}</h1>
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
            <th>Sr#</th>
            <th>Centre</th>
            <th>Region</th>
            <th>City</th>
            <th>Monthly Target</th>
            <th>Monthly Achieved</th>
            <th>Percentage</th>
        </tr>
        @if(count($reportData))
            <?php $monthly_target_total = 0; $monthly_achived_total = 0; $count= 1?>
            @foreach($reportData as $reportsingle)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$reportsingle['name']}}</td>
                    <td>{{$reportsingle['region']}}</td>
                    <td>{{$reportsingle['city']}}</td>
                    <td>{{number_format($reportsingle['monthly_target'],2)}}</td>
                    <td>{{number_format($reportsingle['target_achieved'],2)}}</td>
                    <td>{{number_format($reportsingle['Pecentage'],2)}}%</td>
                    @php
                        $monthly_target_total+=$reportsingle['monthly_target'];
                        $monthly_achived_total+=$reportsingle['target_achieved'];
                    @endphp
                </tr>
            @endforeach
            <tr style="background-color:#3aaddc;color: #fff;">
                <td>Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{number_format($monthly_target_total,2)}}</td>
                <td>{{number_format($monthly_achived_total,2)}}</td>
                <td>{{number_format(($monthly_achived_total/$monthly_target_total)*100,2)}}%</td>
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

</body>
</html>