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
            color: #fff;background-color: #364150;
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
                <td>Patients by Appointment Status (Date Wise)</td>
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
        <tr style="background: #364150; color: #fff;">
            <th>Region</th>
            <th>Centre</th>
            <th>Date</th>
            <th colspan="11" style="text-align: center;">Patient Information</th>
        </tr>
        <tr class="sh-docblue">
            <th colspan="3">&nbsp;</th>
            <th>Sr.</th>
            <td>ID</td>
            <th>Patient</th>
            <th>Email</th>
            <th>Scheduled</th>
            <th>Doctor</th>
            <th>Type</th>
            <th>Consultancy Type</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Referred By</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $region)
                @if(count($region['centres']))
                    <tr>
                        <th colspan="14">{{ $region['name'] }}</th>
                    </tr>
                    @foreach($region['centres'] as $centre)
                        <tr>
                            <th></th>
                            <th colspan="13">{{ $centre['name'] }}</th>
                        </tr>
                        @if(count($centre['dates']))
                            @foreach($centre['dates'] as $data)
                                @if(count($data['appointments']))
                                    <tr>
                                        <th colspan="2"></th>
                                        <th colspan="12">{{ $data['date'] }}</th>
                                    </tr>
                                    @php($sr = 1)
                                    @foreach($data['appointments'] as $appointment)
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ $sr++ }}</td>
                                            <td> {{ $appointment['patient_id'] }}</td>
                                            <td>{{ $appointment['name'] }}</td>
                                            <td>{{ $appointment['email'] }}</td>
                                            <td>{{ $appointment['scheduled_date'] }}</td>
                                            <td>{{ $appointment['doctor_name'] }}</td>
                                            <td>{{ $appointment['appointment_type_name'] }}</td>
                                            <td>{{ $appointment['consultancy_type'] }}</td>
                                            <td>{{ $appointment['appointment_status_name'] }}</td>
                                            <td>{{ $appointment['created_by_name'] }}</td>
                                            <td>{{ $appointment['referred_by_name'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
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