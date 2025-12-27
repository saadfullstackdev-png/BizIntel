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
            font-size: 12px;
            padding: 8px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

         table.table tr td{
            padding: 12px;
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
                <td style="padding-left: 400px;">
                    <table style="float: right;">
            <tr>
                <td style="width: 70px;">Name</td>
                <td>Appointments Summary By Status Report</td>
            </tr>
            <tr>
                <td style="width: 70px;">Duration</td>
                <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong></td>
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
        <tr class="shdoc-header">
            <th width="10%">Region</th>
            <th width="25%">Centre</th>
            <th>Appointment Status</th>
            <th>Total Appointments</th>
        </tr>
        @if(count($reportData))
            @php($grand_total = 0)
            @foreach($reportData as $region)
                @if(count($region['centres']))
                    <tr>
                        <th colspan="4">{{ $region['name'] }}</th>
                    </tr>
                    @php($region_appointments = 0)
                    @foreach($region['centres'] as $centre)
                        <tr>
                            <th></th>
                            <th colspan="3">{{ $centre['name'] }}</th>
                        </tr>
                        @php($centre_appointments = 0)
                        @if(count($centre['appointment_statuses']))
                            @foreach($centre['appointment_statuses'] as $appointment_statuse)
                                @php($centre_appointments = $centre_appointments + $appointment_statuse['total_appointments'])
                                <tr>
                                    <td colspan="2"></td>
                                    <td>{{ $appointment_statuse['name'] }}</td>
                                    <td style="text-align: right;">{{ number_format($appointment_statuse['total_appointments']) }}</td>
                                </tr>
                            @endforeach
                            @php($region_appointments = $region_appointments + $centre_appointments)
                            <tr style="background-color: #37abdc; color: #fff">
                                <th colspan="3" style="text-align: right;">Total for {{ $centre['name'] }}</th>
                                <th style="text-align: right;">{{ number_format($centre_appointments) }}</th>
                            </tr>
                        @endif
                    @endforeach
                    @php($grand_total = $grand_total + $region_appointments)
                    <tr>
                        <th colspan="3" style="text-align: right;">Total for {{ $region['name'] }}</th>
                        <th style="text-align: right;">{{ number_format($region_appointments) }}</th>
                    </tr>
                @endif
            @endforeach
            <tr class="shdoc-header">
                <th colspan="3" style="text-align: right;">Total for All Regions</th>
                <th style="text-align: right;">{{ number_format($grand_total) }}</th>
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