<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
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
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'GENERAL REPORT SUMMARY' }}</h1>
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
        @if(count($reportData2))
            <?php $Gtotalcount = 0;?>
            @foreach($reportData2 as $key => $reporttype)
                <tr class="shdoc-header">
                    @if ( $key === config('constants.appointment_type_consultancy'))

                        <th>Consultancy Name</th>


                    @elseif( $key === config('constants.appointment_type_service') )

                        <th>Treatment Name</th>


                    @endif
                    <th>Count</th>
                </tr>
                <?php $totalcount = 0;?>
                @foreach($reporttype as $reportcount)
                    <tr>
                        <td>{{$reportcount['name']}}</td>
                        <td>{{number_format($reportcount['count'])}}</td>
                        <?php
                        $totalcount += $reportcount['count'];
                        $Gtotalcount += $reportcount['count']
                        ?>
                    </tr>
                @endforeach
                @if( $key === config('constants.appointment_type_consultancy'))
                    <tr class="sh-docblue">
                        <td><label style="font-weight: bold;"> {{ config('constants.Consultancy') }}</label></td>
                        <td style="font-weight: bold;">{{number_format($totalcount)}}</td>
                    </tr>
                @elseif ( $key === config('constants.appointment_type_service'))
                    <tr class="sh-docblue">
                        <td><label style="font-weight: bold;"> {{ config('constants.Service') }}</label></td>
                        <td style="font-weight: bold;">{{number_format($totalcount)}}</td>
                    </tr>
                @endif
            @endforeach
            <tr style="background: #364150;">
                <td><label style="font-weight: bold;color: #fff;"> Grand Total</label></td>
                <td style="font-weight: bold;color: #fff;">{{number_format($Gtotalcount)}}</td>
            </tr>
        @else
            @if($message)
                <tr>
                    <td colspan="12" align="center">{{$message}}</td>
                </tr>
            @endif
        @endif
    </table>
</div>
</body>
</html>