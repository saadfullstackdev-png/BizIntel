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
            <h1>{{ 'DTR Report' }}</h1>
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
                    <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}
                    </td>
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
            <th>Centre</th>
            <th>Region</th>
            <th>City</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Target Service</th>
            <th>Target Service Completed</th>
            <th>Ratio</th>
            <th>Remaining Days</th>
        </tr>
        @if(count($reportData))
            <?php $g_target_service = 0; $g_target_service_complete = 0; ?>
            @foreach($reportData as $reportlocationdata)
                <tr style="background-color: #364150; color:white">
                    <td>{{$reportlocationdata['location']}}</td>
                    <td>{{$reportlocationdata['region']}}</td>
                    <td>{{$reportlocationdata['city']}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $target_service = 0; $target_service_complete = 0; ?>
                @foreach($reportlocationdata['doctors'] as $reportRow )
                    <tr>
                        <?php
                        $target_service+=$reportRow['target_service_count'];
                        $target_service_complete+=$reportRow['target_service_done'];
                        $g_target_service+=$reportRow['target_service_count'];
                        $g_target_service_complete+=$reportRow['target_service_done'];
                        ?>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{$reportRow['doctor']}}</td>
                        <td>{{$reportRow['service']}}</td>
                        <td>{{$reportRow['target_service_count']}}</td>
                        <td>{{$reportRow['target_service_done']}}</td>
                        <td>{{number_format($reportRow['target_complete_ratio'],1).'%'}}</td>
                        <td>{{$reportRow['remaining_day']}}</td>
                    </tr>
                @endforeach
                <tr style="color: #37abdc !important;font-weight: bold;">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Total</td>
                    <td></td>
                    <td>{{$target_service}}</td>
                    <td>{{$target_service_complete}}</td>
                    <td>{{number_format(($target_service_complete/$target_service)*100,1).'%'}}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="color: #37abdc !important;font-weight: bold;">
                <td>Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{$g_target_service}}</td>
                <td>{{$g_target_service_complete}}</td>
                <td>{{number_format(($g_target_service_complete/$g_target_service)*100,1).'%'}}</td>
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
</body>
</html>