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
                        <td>Center Performance Stats By Service Type Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td>{{ Carbon\Carbon::now()->format('Y-m-d') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table">
        <tr style="background: #364150; color: #fff;">
            <th>ID</th>
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
            <th>Service Price</th>
            <th>Invoiced Price</th>
            <th>Created By</th>
        </tr>
        @if(count($reportData))
<?php
            $grandServicePrice = 0; $grandTotalService = 0;  $servicetotal = 0; $grandCount = 0;
?>
            @foreach($reportData as $reportRow)
<?php
                $thisTreatmentTotalPrice = 0; $thisTreatmentInvoicedPrice = 0;$salestotal = 0;$thisTreatmentTotalCount = 0;
?>
                <tr>
                    <td style="font-weight: bold">{{ isset($reportRow['name']) ? $reportRow['name'] : '-' }}</td>
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
                @foreach($reportRow['records'] as $thisDoctor)
                    <?php $thisDoctorServicePrice = 0;$thisDoctorInvoicedPrice = 0;$thisDoctorRecordsCount = 0;?>
                    <tr>
                        <td style="font-weight: bold">{{ $thisDoctor['name'] }}</td>
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
                    @foreach($thisDoctor['appointments'] as $thisAppointment)
                        <?php
                        $grandServicePrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $grandCount += 1;
                        $thisTreatmentTotalPrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                        $thisTreatmentTotalCount += 1;
                        ?>
                        <tr>
                            <td>{{ $thisAppointment->patient_id }}</td>
                            <td>@if($request->get('medium_type') == 'web')
                                    <a target="_blank"
                                       href="{{ route('admin.patients.preview',[$thisAppointment->patient->id]) }}">{{ $thisAppointment->patient->name }}</a>
                                @else
                                    {{ $thisAppointment->patient->name }}
                                @endif
                            </td>
                            <td>{{ ($thisAppointment->created_at) ? \Carbon\Carbon::parse($thisAppointment->created_at, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($thisAppointment->created_at, null)->format('h:i A') : '-' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->doctor_id, $filters['doctors'])) ? $filters['doctors'][$thisAppointment->doctor_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->name : '' }}</td>
                            <td style="text-align: right;">
                                {{ $thisAppointment->patient->email }}
                            </td>
                            <td style="text-align: right;">
                                {{ ($thisAppointment->scheduled_date) ? \Carbon\Carbon::parse($thisAppointment->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($thisAppointment->scheduled_time, null)->format('h:i A') : '-' }}
                            </td>
                            <td>{{ (array_key_exists($thisAppointment->city_id, $filters['cities'])) ? $filters['cities'][$thisAppointment->city_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->location_id, $filters['locations'])) ? $filters['locations'][$thisAppointment->location_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$thisAppointment->base_appointment_status_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$thisAppointment->appointment_type_id]->name : '' }}</td>
                            <td>
                                {{-- dd($thisAppointment->service_id) --}}
                                <?php
                                $serviceprice = (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                                $servicetotal += $serviceprice;
                                $thisDoctorServicePrice += $serviceprice;
                                echo number_format($serviceprice, 2);
                                ?>
                            </td>
                            <td>
                                <?php
                                $salestotal += $thisAppointment->Salestotal;
                                echo number_format($thisAppointment->Salestotal, 2);
                                $grandTotalService += $thisAppointment->Salestotal;
                                $thisDoctorInvoicedPrice += $thisAppointment->Salestotal;
                                $thisTreatmentInvoicedPrice += $thisAppointment->Salestotal;
                                $thisDoctorRecordsCount += 1;
                                ?>
                            </td>
                            <td>{{ (array_key_exists($thisAppointment->created_by, $filters['users'])) ? $filters['users'][$thisAppointment->created_by]->name : '' }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold">{{ $thisDoctor['name'] }}</td>
                        <td style="font-weight: bold">Appointments: {{ $thisDoctorRecordsCount }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ number_format($thisDoctorServicePrice,2) }}</td>
                        <td>{{ number_format($thisDoctorInvoicedPrice,2) }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style="background-color: #37abdc;color: #fff;font-weight: bold;">
                    <td>{{ isset($reportRow['name']) ? $reportRow['name'] : '-' }}</td>
                    <td>Total</td>
                    <td>{{ $thisTreatmentTotalCount }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="color: #fff;"><?php echo number_format($thisTreatmentTotalPrice, 2);?></td>
                    <td style="color: #fff;"><?php echo number_format($thisTreatmentInvoicedPrice, 2);?></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;font-weight: bold">
                <td></td>
                <td style="text-align: center;">Grand Total</td>
                <td>{{ $grandCount }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><?php echo number_format($grandServicePrice, 2);?></td>
                <td style="text-align: right;"><?php echo number_format($grandTotalService, 2);?></td>
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

</body>
</html>