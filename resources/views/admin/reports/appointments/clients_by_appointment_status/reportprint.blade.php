@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title" style="width: 100%">
            <h1>{{ 'Patients by Appointment Status (Date Wise)' }}</h1>
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
        <thead>
        <tr>
            <th>Region</th>
            <th>Centre</th>
            <th>Date</th>
            <th colspan="11" style="text-align: center;">Patient Information</th>
        </tr>
        <tr class="sh-docblue">
            <th colspan="3">&nbsp;</th>
            <th>Sr.</th>
            <th>ID</th>
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
        </thead>
        <tbody>
        @if(count($reportData))
            @foreach($reportData as $region)
                @if(count($region['centres']))
                    <tr>
                        <td colspan="14" style="text-align:left; font-weight: 600;">{{ $region['name'] }}</td>
                    </tr>
                    @foreach($region['centres'] as $centre)
                        <tr>
                            <td></td>
                            <td colspan="13" style="text-align:left; font-weight: 600;">{{ $centre['name'] }}</td>
                        </tr>
                        @if(count($centre['dates']))
                            @foreach($centre['dates'] as $data)
                                @if(count($data['appointments']))
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="12" style="text-align:left; font-weight: 600;">{{ $data['date'] }}</td>
                                    </tr>
                                    @php($sr = 1)
                                    @foreach($data['appointments'] as $appointment)
                                        <tr>
                                            <td colspan="3"></td>
                                            <td>{{ $sr++ }}</td>
                                            <td> {{ $appointment['patient_id'] }}</td>
                                            <td>{{ $appointment['name'] }}</td>
                                            <td>{{ $appointment['phone'] }}</td>
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
        </tbody>
    </table>

</div>
</body>
</html>