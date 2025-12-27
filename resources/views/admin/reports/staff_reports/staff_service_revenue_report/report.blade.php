@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
    @if($request->get('medium_type') == 'pdf')
        @include('partials.pdf_head')
    @else
        @include('partials.head')
    @endif
    <style type="text/css">
        @page {
            margin: 10px 20px;
        }
        @media print {
            table {
                font-size: 12px;
            }
            .tr-root-group {
                background-color: #F3F3F3;
                color: rgba(0, 0, 0, 0.98);
                font-weight: bold;
            }
            .tr-group {
                font-weight: bold;
            }
            .bold-text {
                font-weight: bold;
            }
            .error-text {
                font-weight: bold;
                color: #FF0000;
            }
            .ok-text {
                color: #006400;
            }
        }
    </style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ $reportName }}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('print');">
                    <i class="fa fa-print"></i><span>Print</span>
                </a>
            @endif
        </div>
    </div>
</div>

<div class="panel-body sn-table-body">
    <div class="bordered">
        <div class="sn-table-head">
            <div class="row">
                <div class="col-md-2">
                    <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
                </div>
                <div class="col-md-6">&nbsp;</div>
                <div class="col-md-4">
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
            <div class="table-wrapper" id="topscroll">
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
                           <tr>
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
                           </tr>
                           @foreach($thisDoctor['appointments'] as $thisAppointment)
<?php
                               $grandServicePrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                               $grandCount += 1;
                               $thisTreatmentTotalPrice += (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                               $thisTreatmentTotalCount += 1;

                                $serviceprice = (array_key_exists($thisAppointment->service_id, $filters['services'])) ? $filters['services'][$thisAppointment->service_id]->price : '';
                                $servicetotal += $serviceprice;
                                $thisDoctorServicePrice += $serviceprice;
?>
                            <tr>
                                <td>{{ $thisAppointment->patient_id }}</td>
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
                                    echo number_format($serviceprice, 2);
?>
                                </td>
                                <td>
<?php
                                    $salestotal += $thisAppointment->Salestotal;
                                    echo number_format($salestotal, 2);
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
                        <tr class="sh-docblue">
                            <td>{{ isset($reportRow['name']) ? $reportRow['name'] : '-' }}</td>
                            <td>Total</td>
                            <td><strong>{{ $thisTreatmentTotalCount }}</strong></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?php echo number_format($thisTreatmentTotalPrice,2);?></td>
                            <td><?php echo number_format($thisTreatmentInvoicedPrice,2);?></td>
                            <td></td>
                        </tr>
                    @endforeach
                        <tr class="shdoc-header">
                            <td style="color: #fff;"></td>
                            <td style="text-align: center; color: #fff;"><strong>Grand Total</strong></td>
                            <td style="color: #fff;"><strong>{{ $grandCount }}</strong></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="text-align: right; color: #fff;"><strong><?php echo number_format($grandServicePrice,2);?></strong></td>
                            <td style="text-align: right; color: #fff;"><strong><?php echo number_format($grandTotalService,2);?></strong></td>
                            <td style="color: #fff;"></td>
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
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>