@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf {
            width: 100%;
        }

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

        .table {
            width: 100%;
        }

        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td, th {
            text-align: left;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px 5px;
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
                        <td>Conversion Report For Consultancy</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From {{ $start_date }} to {{ $end_date }}</td>
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
</div>
</body>
</html>