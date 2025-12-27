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
            <h1>{{($performance == 'true')?'My Appointment By Type':'Appointment By Type'}}</h1>
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
        </div>
        <div class="print-time">
            <table class="dark-th-table table table-bordered">
                <tr>
                    <th width="25%">Duration</th>
                    <td>From {{ $start_date }} to {{ $end_date }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>
    <table class="table">
        <thead>
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
        </thead>
        <tbody>
        @if(count($reportData))
            @foreach($reportData as $reporttype)
                @php
                    $count = 0;
                @endphp
                <tr style="font-weight: bold">
                    <td>{{$reporttype['type_name']}}</td>
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
                @foreach($reporttype['appointment_data'] as $appointmentdata)
                    <tr>
                        <td>{{ $appointmentdata['appointment_id'] }}</td>
                        <td>{{ $appointmentdata['patient_name'] }}</td>
                        <td>{{ \App\Helpers\GeneralFunctions::prepareNumber4Call($appointmentdata['patient_phone']) }}</td>
                        <td>{{ $appointmentdata['patient_email'] }}</td>
                        <td>{{ $appointmentdata['scheduled_at'] }}</td>
                        <td>{{ $appointmentdata['doctor_name'] }}</td>
                        <td>{{ $appointmentdata['city'] }}</td>
                        <td>{{ $appointmentdata['centre'] }}</td>
                        <td>{{ $appointmentdata['consultancy'] }}</td>
                        <td>{{ $appointmentdata['status'] }}</td>
                        <td>{{ $appointmentdata['type'] }}</td>
                        <td>{{ $appointmentdata['created_at'] }}</td>
                        <td>{{ $appointmentdata['created_by'] }}</td>
                        <td>{{ $appointmentdata['converted_by'] }}</td>
                        <td>{{ $appointmentdata['rescheduled_by'] }}</td>
                        <td>{{ $appointmentdata['referred_by'] }}</td>
                        @php
                            $count++
                        @endphp
                    </tr>
                @endforeach
                <tr style="background-color:#364150;color: #fff;">
                    <td style="color: #fff">Total</td>
                    <td style="color: #fff">{{number_format($count)}}</td>
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
                <td colspan="14" align="center">No record round.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
</div>

</body>
</html>