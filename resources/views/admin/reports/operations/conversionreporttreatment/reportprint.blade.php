@inject('request', 'Illuminate\Http\Request')
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
            <h1>{{ 'Conversion Report For Treatment' }}</h1>
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
                    <td>From {{ $start_date }} to {{ $end_date }}
                    </td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table">
        <tr>
            <th>Region</th>
            <th>Centre</th>
            <th>Booked T's</th>
            <th>Arrived T's</th>
            <th>Arrival Ratio(%)</th>
        </tr>
        @if(count($reportData))
            @php $g_total = 0; $g_arrived = 0;@endphp
            @foreach($reportData as $region)
                <tr>
                    <td colspan="5"><strong>{{ $region['name'] }}</strong></td>
                </tr>
                @php $t_total = 0; $t_arrived = 0;@endphp
                @foreach($region['location'] as $centre)
                    <tr>
                        <td></td>
                        <td>{{$centre['location_name']}}</td>
                        <td>{{$centre['booked']}}</td>
                        <td>{{$centre['arrived']}}</td>
                        <td>{{number_format($centre['arrival_ratio'],2)}}%</td>
                    </tr>
                    @php
                        $t_total += $centre['booked'];
                        $t_arrived += $centre['arrived'];
                        $g_total += $centre['booked'];
                        $g_arrived += $centre['arrived'];
                    @endphp
                @endforeach
                <tr style="background-color: #37abdc; color: #fff">
                    <td>Total</td>
                    <td></td>
                    <td>{{number_format($t_total)}}</td>
                    <td>{{number_format($t_arrived)}}</td>
                    <td>{{number_format(($t_arrived/$t_total)*100,2)}}%</td>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff;">
                <td>Grand Total</td>
                <td></td>
                <td>{{number_format($g_total)}}</td>
                <td>{{number_format($g_arrived)}}</td>
                <td>{{number_format(($g_arrived/$g_total)*100,2)}}%</td>
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