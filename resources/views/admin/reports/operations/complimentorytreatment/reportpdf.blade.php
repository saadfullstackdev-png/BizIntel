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
                <td style="padding-left: 450px;">
                    <table style="float: right;">
            <tr>
                <td style="width: 70px;">Name</td>
                 <td>Complimentory Treatments Report</td>
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
</div>

</body>
</html>