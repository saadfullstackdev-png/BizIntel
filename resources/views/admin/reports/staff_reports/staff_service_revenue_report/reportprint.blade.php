@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ $reportName }}</h1>
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
        <th style="vertical-align: middle;">ID</th>
        <th style="vertical-align: middle;">Client Name</th>
        <th style="vertical-align: middle;">Created At</th>
        <th style="vertical-align: middle;">Doctor</th>
        <th style="vertical-align: middle;">Service</th>
        <th style="vertical-align: middle;">Email</th>
        <th style="vertical-align: middle;">Scheduled</th>
        <th style="vertical-align: middle;">City</th>
        <th style="vertical-align: middle;">Centre</th>
        <th style="vertical-align: middle;">Status</th>
        <th style="vertical-align: middle;">Type</th>
        <th style="vertical-align: middle;">Service Price</th>
        <th style="vertical-align: middle;">Invoiced Price</th>
        <th style="vertical-align: middle;">Created By</th>
        </thead>
        <tbody>
        @if(count($reportData))
            <?php $grandServicePrice = 0; $grandTotalService = 0;  $servicetotal = 0; $grandCount = 0; ?>
            @foreach($reportData as $reportRow)
                {{-- dd($reportRow) --}}
                <?php
                $thisTreatmentTotalPrice = 0;
                $thisTreatmentInvoicedPrice = 0;
                $salestotal = 0;
                $thisTreatmentTotalCount = 0;
                ?>
                <tr>
                    <td><strong>{{ isset($reportRow['name']) ? $reportRow['name'] : '-' }}</strong></td>
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
                {{-- dd($reportRow['records']) --}}
                @foreach($reportRow['records'] as $thisDoctor)
                    {{-- dd($thisDoctor) --}}
                    <?php
                    $thisDoctorServicePrice = 0;
                    $thisDoctorInvoicedPrice = 0;
                    $thisDoctorRecordsCount = 0;
                    ?>
                    <tr style="background-color:#3aaddc;color: #fff;">
                        <td><strong>{{ $thisDoctor['name'] }}</strong></td>
                        <td>{{-- $thisDoctor['centre'] --}}</td>
                        <td>{{-- $thisDoctor['region'] --}}</td>
                        <td>{{-- $thisDoctor['city'] --}}</td>
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
                            <td> {{ $thisAppointment->patient_id }}</td>
                            <td style="text-align: center;">@if($request->get('medium_type') == 'web')
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
                        <td><strong>{{ $thisDoctor['name'] }}</strong></td>
                        <td><strong>Appointments: {{ $thisDoctorRecordsCount }}</strong></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><strong>{{ number_format($thisDoctorServicePrice,2) }}</strong></td>
                        <td><strong>{{ number_format($thisDoctorInvoicedPrice,2) }}</strong></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style="background-color: #37abdc;color: #fff;font-weight: bold;">
                    <td style="text-align: center;"><strong>{{ isset($reportRow['name']) ? $reportRow['name'] : '-' }}</strong></td>
                    <td><strong>Total</strong></td>
                    <td><strong>{{ $thisTreatmentTotalCount }}</strong></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;"></td>
                    <td style="text-align: right;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><strong><?php echo number_format($thisTreatmentTotalPrice,2);?></strong></td>
                    <td><strong><?php echo number_format($thisTreatmentInvoicedPrice,2);?></strong></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff; font-weight: bold">
                <td></td>
                <td style="text-align: center;"><strong>Grand Total</strong></td>
                <td><strong>{{ $grandCount }}</strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><strong><?php echo number_format($grandServicePrice,2);?></strong></td>
                <td style="text-align: right;"><strong><?php echo number_format($grandTotalService,2);?></strong></td>
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