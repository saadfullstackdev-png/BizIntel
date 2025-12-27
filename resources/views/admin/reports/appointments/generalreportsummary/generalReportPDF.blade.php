@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>

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
            padding: 8px;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
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

        .shdoc-header {
            background: #364150;
            color: #fff;
        }
    </style>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
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
                        <td>General Report Summary</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong>
                        </td>
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
</div>

</body>
</html>