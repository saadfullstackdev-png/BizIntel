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
            <h1>{{ ($performance) ? 'My Appointments By Status Report' : 'Appointments By Status Report'}}</h1>
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
            @foreach( $reportData as $reportRow )
                <?php $count = 0;?>
                <tr style="font-weight: bold">
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
                <tr style="background-color:#364150;color: #fff;font-weight:bold">
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
        </tbody>
    </table>
</div>
</div>

</body>
</html>