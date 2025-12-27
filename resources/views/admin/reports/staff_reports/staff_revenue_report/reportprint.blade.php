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
            <h1>{{ 'Staff Revenue Centre Wise' }}</h1>
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
        </thead>
        <tbody>
        @if(count($reportData))
            <?php $count = 0;$salesgrandtotal = 0; $servicegrandtotal = 0; $grandcount = 0  ?>
            @foreach($reportData as $reportpackagedata)
                <tr style="font-weight: bold">
                <td><?php echo $reportpackagedata['name']; ?></td>
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
                <?php $count = 0;$salestotal = 0; $servicetotal = 0;?>
                @foreach($reportpackagedata['records'] as $reportRow )
                    <tr>
                        <td>{{ $reportRow['name'] }}</td>
                        <td>{{--Appointments: --}}{{-- count($reportRow['appointments']) --}}</td>
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
                            {{--                                @if($loop->last)
                                                                {{ $thisAppointment }}
                                                            @endif
                            --}}
                            <td>{{ $thisAppointment->patient_id }}</td>
                            <td>
                                @if($request->get('medium_type') == 'web')
                                    <a target="_blank"
                                       href="{{ route('admin.patients.preview',[$thisAppointment->patient->id]) }}">{{ $thisAppointment->patient->name }}</a>
                                @else
                                    {{ $thisAppointment->patient->name }}
                                @endif
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
                                echo number_format($thisAppointment->Salestotal, 2);
                                $thisDoctorInvoicePrice += $thisAppointment->Salestotal;
?>
                                {{-- $thisAppointment->Salestotal --}}
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
                <tr style="background-color: #37abdc;color: #fff;font-weight: bold;">
                    <td><?php echo $reportpackagedata['name']; ?></td>
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
            <tr style="background: #364150; color: #fff; font-weight: bold">
                <td></td>
                <td style="color: #fff;">Grand Total</td>
                <td style="color: #fff;">{{$grandcount}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo number_format($servicegrandtotal, 2); ?></td>
                <td><?php echo number_format($salesgrandtotal, 2); ?></td>
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
</div>

</body>
</html>