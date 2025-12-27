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
            <h1>{{ 'No Show List Report'  }}</h1>
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
        <tr style="background-color: rgb(54, 65, 80); color:#fff;">
            <th>ID</th>
            <th>Client</th>
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
        </tr>
        </thead>
        <tbody>
        @if(count($appointment_info))
            @foreach($appointment_info as $reportRow)
                <tr>
                    <td>{{ $reportRow->patient_id ? $reportRow->patient_id : '' }}</td>
                    <td>{{ $reportRow->patient_name ? $reportRow->patient_name : '' }}</td>
                    <td>{{ $reportRow->patient_email ? $reportRow->patient_email : '' }}</td>
                    <td>{{ ($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-' }}</td>
                    <td>{{ (array_key_exists($reportRow->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportRow->doctor_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->city_id, $filters['cities'])) ? $filters['cities'][$reportRow->city_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportRow->appointment_type_id]->name : '' }}</td>
                    <td>{{ \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A') }}</td>
                    <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->updated_by, $filters['users'])) ? $filters['users'][$reportRow->updated_by]->name : '' }}</td>
                </tr>
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
</div>

</body>
</html>