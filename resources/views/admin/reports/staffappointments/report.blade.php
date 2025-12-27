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
            <h1>{{ 'Staff Appointment Schedule Report'  }}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('print');">
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
        </div>
        <div class="table-wrapper" id="topscroll">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Created By</th>
                    <th>Updated By</th>
                    <th>Rescheduled By</th>
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
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $count = 0;$salesgrandtotal = 0; $servicegrandtotal = 0; $grandcount = 0  ?>
                    @foreach($reportData as $reportpackagedata)
                        <tr role="row">
                            <td colspan="17"><?php echo $reportpackagedata['name']; ?></td>
                        </tr>
                        <?php $count = 0;$salestotal = 0; $servicetotal = 0;?>
                        @foreach($reportpackagedata['records'] as $reportRow )

                            @if ($reportRow->consultancy_type == 'in_person')
                                @php $consultancy_type = 'In Person'; @endphp
                            @elseif($reportRow->consultancy_type == 'virtual')
                                @php $consultancy_type = 'Virtual';@endphp
                            @else
                                @php $consultancy_type = '';@endphp
                            @endif

                            <tr role="row">
                                <td> {{ $reportRow->patient_id }}</td>
                                <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                                <td>{{ (array_key_exists($reportRow->converted_by, $filters['users'])) ? $filters['users'][$reportRow->converted_by]->name : '' }}</td>
                                <td>{{ (array_key_exists($reportRow->updated_by, $filters['users'])) ? $filters['users'][$reportRow->updated_by]->name : '' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A') }}</td>
                                <td>
                                    @if($request->get('medium_type') == 'web')
                                        <a target="_blank"
                                           href="{{ route('admin.patients.preview',[$reportRow->patient->id]) }}">{{ $reportRow->patient->name }}</a>
                                    @else
                                        {{ $reportRow->patient->name }}
                                    @endif
                                </td>
                                <td>{{ (array_key_exists($reportRow->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportRow->doctor_id]->name : '' }}</td>
                                <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                                <td>{{ $reportRow->patient->email }}</td>
                                <td>{{ ($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-' }}</td>
                                <td style="text-align: right;"><?php
                                    $serviceprice = (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
                                    $servicetotal += $serviceprice;
                                    echo number_format($serviceprice, 2);
                                    ?>
                                </td>
                                <td style="text-align: right;">
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

                        {{--<tr role="row" class="separator">--}}
                        <tr class="sh-docblue">
                            <td colspan="2"><?php echo $reportpackagedata['name']; ?></td>
                            <td>Total</td>
                            <td>{{$count}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;">
                                <?php
                                $servicegrandtotal += $servicetotal;
                                echo number_format($servicetotal, 2);
                                ?>
                            </td>
                            <td style="text-align: right;">
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
                    <tr class="total">
                        <td></td>
                        <td></td>
                        <td>Grand Total</td>
                        <td>{{$grandcount}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;"><?php echo number_format($servicegrandtotal, 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($salesgrandtotal, 2); ?></td>
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
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>