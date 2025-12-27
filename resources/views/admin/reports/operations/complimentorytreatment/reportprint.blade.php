@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Complimentory Treatments Report' }}</h1>
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
        <tr>
            <th>ID</th>
            <th>Client Name</th>
            <th>Email</th>
            <th>City</th>
            <th>Centre</th>
            <th>Appointment Type</th>
            <th>Appointment Status</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Created At</th>
            <th>Scheduled</th>
            <th>Invoice Status</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $reportRow)
                <tr>
                    <td> {{ $reportRow->patient_id }}</td>
                    <td>{{$reportRow->name}}</td>
                    <td>{{$reportRow->Patient_email ? $reportRow->Patient_email : ''}}</td>
                    <td>{{$filters['cities'][$reportRow->city_id]->name}}</td>
                    <td>{{$filters['locations'][$reportRow->location_id]->name}}</td>
                    <td>{{$filters['appointment_types'][$reportRow->appointment_type_id]->name}}</td>
                    <td>{{$filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name}}</td>
                    <td>{{$filters['doctors'][$reportRow->doctor_id]->name}}</td>
                    <td>{{$filters['services'][$reportRow->service_id]->name}}</td>
                    <td>{{ \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A')}}</td>
                    <td>{{($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-' }}</td>
                    <td>{{$reportRow->invoices}}</td>
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
    </table>
</div>
</body>
</html>