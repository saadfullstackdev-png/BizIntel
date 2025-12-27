@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf {
            width: 100%;
        }

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

        .table {
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
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px 5px;
        }

        table.table tr:first-child {
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
                        <td>
                            <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}"
                                 class="img-responsive" alt=""/>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding-left: 450px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Client With Not Completed Treatment Report</td>
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
        <tr style="background-color: #334150; color:#fff;">
            <td>ID</td>
            <th>Client Name</th>
            <th>Created At</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Email</th>
            <th>Scheduled</th>
            <th>City</th>
            <th>Centre</th>
            <th>Status</th>
            <th>Type</th>
            <th>Created by</th>
        </tr>
        @if(count($leadAppointmentData))
            <?php $grandcount = 0;?>
            @foreach($leadAppointmentData as $reportlead)
                <tr>
                    <td></td>
                    <td><?php echo $reportlead['name']; ?></td>
                    <td>{{ (array_key_exists($reportlead['region_id'], $filters['regions'])) ? $filters['regions'][$reportlead['region_id']]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportlead['city_id'], $filters['cities'])) ? $filters['cities'][$reportlead['city_id']]->name : '' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $count = 0;?>
                @foreach($reportlead['children'] as $reportAppointments )
                    <tr>
                        <td> {{ $reportAppointments->patient->id }}</td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                                <a target="_blank"
                                   href="{{ route('admin.patients.preview',[$reportAppointments->patient->id]) }}">{{ $reportAppointments->patient->name }}</a>
                            @else
                                {{ $reportAppointments->patient->name }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reportAppointments->created_at)->format('M j, Y H:i A') }}</td>
                        <td>{{ (array_key_exists($reportAppointments->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportAppointments->doctor_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->service_id, $filters['services'])) ? $filters['services'][$reportAppointments->service_id]->name : '' }}</td>
                        <td>{{ $reportAppointments->patient->email }}</td>
                        <td>{{ ($reportAppointments->scheduled_date) ? \Carbon\Carbon::parse($reportAppointments->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportAppointments->scheduled_time, null)->format('h:i A') : '-' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->city_id, $filters['cities'])) ? $filters['cities'][$reportAppointments->city_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->location_id, $filters['locations'])) ? $filters['locations'][$reportAppointments->location_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportAppointments->base_appointment_status_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportAppointments->appointment_type_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->created_by, $filters['users'])) ? $filters['users'][$reportAppointments->created_by]->name : '' }}</td>
                    </tr>
                    <?php $count++; $grandcount++; ?>
                @endforeach
                <tr style="background-color:#3aaddc;color: #fff;">
                    <td></td>
                    <td><?php echo $reportlead['name']; ?></td>
                    <td>Total</td>
                    <td>{{$count}}</td>
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
            <tr style=" background: #364150; color: #fff; font-weight: bold">
                <td></td>
                <td>Grand Total</td>
                <td>{{$grandcount}}</td>
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