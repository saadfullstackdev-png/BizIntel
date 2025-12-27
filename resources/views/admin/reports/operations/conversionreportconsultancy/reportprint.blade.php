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
            <h1>{{ 'Conversion Report For Consultancy' }}</h1>
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
                    <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>
    <table class="table">
        <tr>
            <th>Region</th>
            <th>Centre</th>
            <th>Booked GC's</th>
            <th>Arrived GC's</th>
            <th>Arrival Ratio(%)</th>
            <th>Converted GC's</th>
            <th>Converted Ratio(%)</th>
        </tr>
        @if(count($reportData))
            @php $g_total = 0; $g_arrived = 0; $g_converted = 0;@endphp
            @foreach($reportData as $region)
                <tr>
                    <td colspan="7"><strong>{{ $region['name'] }}</strong></td>
                </tr>
                @php $t_total = 0; $t_arrived = 0; $t_converted = 0; @endphp
                @foreach($region['location'] as $centre)
                    <tr>
                        <td></td>
                        <td>{{$centre['location_name']}}</td>
                        <td>{{$centre['booked']}}</td>
                        <td>{{$centre['arrived']}}</td>
                        <td>{{number_format($centre['arrival_ratio'],2)}}%</td>
                        <td>{{$centre['converted']}}</td>
                        <td>{{number_format($centre['conversion_ratio'],2)}}%</td>
                    </tr>
                    @php
                        $t_total += $centre['booked'];
                        $t_arrived += $centre['arrived'];
                        $t_converted += $centre['converted'];
                        $g_total += $centre['booked'];
                        $g_arrived += $centre['arrived'];
                        $g_converted += $centre['converted'];
                    @endphp
                @endforeach
                <tr style="background-color: #37abdc; color: #fff">
                    <td>Total</td>
                    <td></td>
                    <td>{{number_format($t_total)}}</td>
                    <td>{{number_format($t_arrived)}}</td>
                    <td>{{$t_total > 0?number_format(($t_arrived/$t_total)*100,2):0}}%</td>
                    <td>{{number_format($t_converted)}}</td>
                    <td>{{$t_arrived > 0?number_format(($t_converted/$t_arrived)*100,2):0}}%</td>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff;">
                <td>Grand Total</td>
                <td></td>
                <td>{{number_format($g_total)}}</td>
                <td>{{number_format($g_arrived)}}</td>
                <td>{{$g_total > 0?number_format(($g_arrived/$g_total)*100,2):0}}%</td>
                <td>{{number_format($g_converted)}}</td>
                <td>{{$g_arrived?number_format(($g_converted/$g_arrived)*100,2):0}}%</td>
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