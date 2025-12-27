@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf{
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
        .table{
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
        table.table tr td{
            padding: 12px 5px;
        }
        table.table tr:first-child{
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
                        <td>DTR Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>For the month of <strong>{{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</strong></td>
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
</div>

</body>
</html>