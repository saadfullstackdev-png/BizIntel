@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <title>{{ ( $performance ) ? 'My Appointments By Status Report' : 'Appointments By Status Report' }}</title>
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
            padding: 5px;
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
                        <td>{{ ($performance === 'true') ? 'My Appointments By Status Report' : 'Appointments By Status Report' }}</td>
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
        <tr style="background-color:#364150;color: #fff;">
            <th>ID</th>
            <th>Client</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Scheduled</th>
            <th>Doctor</th>
            <th>City</th>
            <th>Centre</th>
            <th>Treatment/Consultancy</th>
            <th>Status</th>
            <th>Type</th>
            <th>Created At</th>
            <th>Created By</th>
            <th>Updated By</th>
            <th>Rescheduled By</th>
            <th>Referred By</th>
        </tr>
        @if(count($reportData))
            @foreach( $reportData as $reportRow )
                <?php $count = 0;?>
                <tr style="font-weight: bold;">
                    <td>{{$reportRow['status_name']}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach( $reportRow['appointment_data'] as $appointment )
                    <tr>
                        <td> {{ $appointment['appointment_id'] }}</td>
                        <td> {{ $appointment['patient_name'] }}</td>
                        <td> {{ App\Helpers\GeneralFunctions::prepareNumber4Call($appointment['patient_phone']) }}</td>
                        <td> {{ $appointment['patient_email'] }}</td>
                        <td> {{ $appointment['scheduled_at'] }}</td>
                        <td> {{ $appointment['doctor_name'] }}</td>
                        <td> {{ $appointment['city'] }}</td>
                        <td> {{ $appointment['centre'] }}</td>
                        <td> {{ $appointment['consultancy'] }}</td>
                        <td> {{ $appointment['status'] }}</td>
                        <td> {{ $appointment['type'] }}</td>
                        <td> {{ $appointment['created_at'] }}</td>
                        <td> {{ $appointment['created_by'] }}</td>
                        <td> {{ $appointment['converted_by'] }}</td>
                        <td> {{ $appointment['rescheduled_by'] }}</td>
                        <td> {{ $appointment['referred_by'] }}</td>
                        @php
                            $count++;
                        @endphp
                    </tr>
                @endforeach
                <tr style="background-color:#364150;color: #fff;">
                    <td style="color: #fff;">Total</td>
                    <td style="color: #fff;">{{number_format($count)}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
        @endif
    </table>
</div>

</body>
</html>