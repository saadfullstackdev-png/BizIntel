@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
<link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Appointments Summary By Status Report' }}</h1>
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
            <th width="10%">Region</th>
            <th width="25%">Centre</th>
            <th>Appointment Status</th>
            <th>Total Appointments</th>
        </tr>
        </thead>
        <tbody>
        @if(count($reportData))
            @php($grand_total = 0)
            @foreach($reportData as $region)
                @if(count($region['centres']))
                    <tr>
                        <td colspan="4">{{ $region['name'] }}</td>
                    </tr>
                    @php($region_appointments = 0)
                    @foreach($region['centres'] as $centre)
                        <tr>
                            <td></td>
                            <td colspan="3">{{ $centre['name'] }}</td>
                        </tr>
                        @php($centre_appointments = 0)
                        @if(count($centre['appointment_statuses']))
                            @foreach($centre['appointment_statuses'] as $appointment_statuse)
                                @php($centre_appointments = $centre_appointments + $appointment_statuse['total_appointments'])
                                <tr>
                                    <td colspan="2"></td>
                                    <td>{{ $appointment_statuse['name'] }}</td>
                                    <td style="text-align: right; padding-right;15px;">{{ number_format($appointment_statuse['total_appointments']) }}</td>
                                </tr>
                            @endforeach
                            @php($region_appointments = $region_appointments + $centre_appointments)
                            <tr style="background-color: #37abdc; color: #fff;font-weight: bold">
                                <td colspan="3" style="text-align: right;">Total for {{ $centre['name'] }}</td>
                                <td style="text-align: right; padding-right;15px;">{{ number_format($centre_appointments) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @php($grand_total = $grand_total + $region_appointments)
                    <tr>
                        <td colspan="3" style="text-align: right;">Total for {{ $region['name'] }}</td>
                        <td style="text-align: right; padding-right;15px;">{{ number_format($region_appointments) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr style="background-color: #364150; color: #fff; font-weight: bold">
                <td colspan="3" style="text-align: right;">Total for All Regions</td>
                <td style="text-align: right; padding-right;15px;">{{ number_format($grand_total) }}</td>
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
        </tbody>
    </table>
    </table>
</div>
</div>

</body>
</html>