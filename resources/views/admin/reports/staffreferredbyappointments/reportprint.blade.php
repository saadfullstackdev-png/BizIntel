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
            <h1>{{ 'Staff (Referred By) Appointment Report'  }}</h1>
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
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

    <div class="table-wrapper">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Client Name</th>
                <th>Doctor</th>
                <th>Service</th>
                <th>Email</th>
                <th>Scheduled</th>
                <th>Service Price</th>
                <th>Invoiced</th>
                <th>City</th>
                <th>Centre</th>
                <th>Status</th>
                <th>Type</th>
                <th>Consultancy Type</th>
            </tr>
            @if(count($reportData))
                <?php $count = 0;$salesgrandtotal = 0; $servicegrandtotal = 0; $grandcount = 0; ?>
                @foreach($reportData as $reportpackagedata)
                    <tr style="background-color: #dddddd">
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
                        <td></td>
                    </tr>
                    <?php $count = 0;$salestotal = 0; $servicetotal = 0; ?>
                    @foreach($reportpackagedata['records'] as $reportRow )

                        @if ($reportRow->consultancy_type == 'in_person')
                            @php $consultancy_type = 'In Person'; @endphp
                        @elseif($reportRow->consultancy_type == 'virtual')
                            @php $consultancy_type = 'Virtual';@endphp
                        @else
                            @php $consultancy_type = '';@endphp
                        @endif

                        <tr>
                            <td> {{ $reportRow->patient_id }}</td>
                            <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A') }}</td>
                            <td>{{ $reportRow->patient->name }}</td>
                            <td>{{ (array_key_exists($reportRow->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportRow->doctor_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                            <td>{{ $reportRow->patient->email }}</td>
                            <td>{{ ($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-' }}</td>
                            <td style="text-align: right">
                                <?php
                                $serviceprice = (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
                                $servicetotal += $serviceprice;
                                echo number_format($serviceprice, 2);
                                ?>
                            </td>
                            <td style="text-align: right">
                                <?php
                                $salestotal += $reportRow->Salestotal;
                                echo number_format($reportRow->Salestotal, 2);
                                ?>
                            </td>
                            <td>{{ (array_key_exists($reportRow->city_id, $filters['cities'])) ? $filters['cities'][$reportRow->city_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($reportRow->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name : '' }}</td>
                            <td>{{ (array_key_exists($reportRow->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportRow->appointment_type_id]->name : '' }}</td>
                            <td>{{ $consultancy_type }}</td>
                        </tr>
                        <?php $count++; $grandcount++; ?>
                    @endforeach
                    <tr style="background-color:#3aaddc;color: #fff;">
                        <td><?php echo $reportpackagedata['name']; ?></td>
                        <td>Total</td>
                        <td>{{$count}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right">
                            <?php
                            $servicegrandtotal += $servicetotal;
                            echo number_format($servicetotal, 2);
                            ?>
                        </td>
                        <td style="text-align: right">
                            <?php
                            $salesgrandtotal += $salestotal;
                            echo number_format($salestotal, 2);
                            ?>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style=" background: #364150; color: #fff; font-weight: bold">
                    <td></td>
                    <td style="color: #fff">Grand Total</td>
                    <td style="color: #fff">{{$grandcount}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;color: #fff"><?php echo number_format($servicegrandtotal, 2); ?></td>
                    <td style="text-align: right;color: #fff"><?php echo number_format($salesgrandtotal, 2); ?></td>
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
</div>

</body>
</html>