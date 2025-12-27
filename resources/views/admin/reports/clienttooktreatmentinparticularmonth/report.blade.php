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
            <h1>{{ 'Client With Treatment In Particular Month Report'  }}</h1>
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
                <tr>
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
                    <th>Created by</th>`
                </tr>
                </thead>
                <tbody>
                @if(count($leadAppointmentData))
                    <?php $grandcount = 0;?>
                    @foreach($leadAppointmentData as $reportlead)
                        <tr style="background-color: #dddddd">
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
                            <td style="color: #fff;"><?php echo $reportlead['name']; ?></td>
                            <td style="color: #fff;">Total</td>
                            <td style="color: #fff;">{{$count}}</td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                        </tr>
                    @endforeach
                    <tr style=" background: #364150; color: #fff; font-weight: bold">
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
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>