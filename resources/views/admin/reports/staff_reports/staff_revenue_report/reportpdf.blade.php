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
        .shdoc-header{
            background: #364150; color: #fff;
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
                        <td>Staff Revenue Centre Wise</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong></td>
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
        <tbody>
        @if(count($reportData))
<?php
            $count = 0;$salesgrandtotal = 0; $servicegrandtotal = 0; $grandcount = 0;
?>
            @foreach($reportData as $reportpackagedata)
                <tr style="font-weight: bold">
                <td>{{ $reportpackagedata['name'] }}</td>
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
<?php
                $count = 0;$salestotal = 0; $servicetotal = 0;
?>
                @foreach($reportpackagedata['records'] as $reportRow )
                    <tr>
                    <td>{{ $reportRow['name'] }}</td>
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
<?php
                    $thisDoctorServicePrice = 0;
                    $thisDoctorInvoicePrice = 0;
?>

                    @foreach($reportRow['appointments'] as $thisAppointment)
                        <tr>
                            <td> {{ $thisAppointment->patient_id }}</td>
                            <td>
                                    {{ $thisAppointment->patient->name }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($thisAppointment->created_at)->format('M j, Y H:i A') }}</td>
                            <td>{{ (array_key_exists($thisAppointment->doctor_id, $filters['doctors'])) ? $filters['doctors'][$thisAppointment->doctor_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->name : '' }}</td>
                            <td>{{ $thisAppointment->patient->email }}</td>
                            <td>{{ ($thisAppointment->scheduled_date) ? \Carbon\Carbon::parse($thisAppointment->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($thisAppointment->scheduled_time, null)->format('h:i A') : '-' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->city_id, $filters['cities'])) ? $filters['cities'][$thisAppointment->city_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->location_id, $filters['locations'])) ? $filters['locations'][$thisAppointment->location_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$thisAppointment->base_appointment_status_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($thisAppointment->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$thisAppointment->appointment_type_id]->name : '' }}</td>
                            <td>
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
                                $thisDoctorInvoicePrice += $thisAppointment->Salestotal;

                                echo number_format($thisAppointment->Salestotal, 2);
?>
                            </td>
                            <td>{{ (array_key_exists($thisAppointment->created_by, $filters['users'])) ? $filters['users'][$thisAppointment->created_by]->name : '' }}</td>
                        </tr>
                        <?php $count++; $grandcount++; ?>
                    @endforeach
                    <tr>
                        <td>{{ $reportRow['name'] }}</td>
                        <td>Appointments: {{ count($reportRow['appointments']) }}</td>
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
                        <td>{{ number_format($thisDoctorInvoicePrice,2) }}</td>
                        <td></td>
                    </tr>

                @endforeach
                <tr style="background-color: #37abdc; color: #fff; font-weight: bold">
                    <td>{{ $reportpackagedata['name'] }}</td>
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
                    <td>
<?php
                        $servicegrandtotal += $servicetotal;
                        echo number_format($servicetotal, 2);
?>
                    </td>
                    <td>
<?php
                        $salesgrandtotal += $salestotal;
                        echo number_format($salestotal, 2);
?>
                    </td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;font-weight: bold">
                <td></td>
                <td style="color: #fff;">Grand Total</td>
                <td style="color: #fff;">{{ $grandcount }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="color: #fff;"><?php echo number_format($servicegrandtotal, 2); ?></td>
                <td style="color: #fff;"><?php echo number_format($salesgrandtotal, 2); ?></td>
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
        </tbody>
    </table>
</div>

</body>
</html>