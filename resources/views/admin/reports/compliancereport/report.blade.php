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
            <h1>{{ 'Compliance Report'  }}</h1>
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
        </div><!-- End sn-table-head -->
        <div class="table-wrapper" id="topscroll">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Scheduled</th>
                    <th>Doctor</th>
                    <th>City</th>
                    <th>Centre</th>
                    <th>Treatment/Consultancy</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Consultancy Type</th>
                    <th>Created At</th>
                    <th>Created By</th>
                    <th>Updated By</th>
                    <th>Rescheduled By</th>
                    <th>Referred By</th>
                    <th>Medical History Form</th>
                    <th>Images Before Service</th>
                    <th>Images After Service</th>
                    <th>Measurement Before Service</th>
                    <th>Measurement After Service</th>
                    <th>Invoice</th>
                </tr>
                </thead>
                <tbody>
                @if(count($reportData))
                    @foreach( $reportData as $reportRow )
                        <tr>

                            <td> {{ $reportRow['id'] }}</td>
                            <td>
                                @if($request->get('medium_type') == 'web')
                                    <a target="_blank" href="{{ route('admin.patients.preview',[$reportRow['client_id']]) }}" >{{ $reportRow['client'] }}</a>
                                @else
                                    {{ $reportRow['client'] }}
                                @endif
                            </td>
                            <td> {{ $reportRow['email'] }}</td>
                            <td> {{ ($reportRow['scheduled_date']) ? \Carbon\Carbon::parse($reportRow['scheduled_date'], null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow['scheduled_time'], null)->format('h:i A') : '-' }}</td>
                            <td> {{ $reportRow['doctor'] }}</td>
                            <td> {{ $reportRow['city'] }}</td>
                            <td> {{ $reportRow['centre'] }}</td>
                            <td> {{ $reportRow['service'] }}</td>
                            <td> {{ $reportRow['status'] }}</td>
                            <td> {{ $reportRow['type'] }}</td>
                            <td> {{ $reportRow['consultancy_type'] }}</td>
                            <td> {{ \Carbon\Carbon::parse($reportRow['created_at'])->format('M j, Y H:i A') }}</td>
                            <td> {{ $reportRow['created_by'] }}</td>
                            <td> {{ $reportRow['converted_by'] }}</td>
                            <td> {{ $reportRow['updated_by'] }}</td>
                            <td> {{ $reportRow['referred_by'] }}</td>
                            @if (array_key_exists('medical_form', $reportRow))
                                <td @if($reportRow['medical_form'] == 'No') style="color: red; font-weight: bold;" @endif>{{ $reportRow['medical_form'] }}</td>
                            @else
                                <td>N/A</td>
                            @endif

                            @if (array_key_exists('images_before', $reportRow))
                                <td @if($reportRow['images_before'] == 'No') style="color: red; font-weight: bold;" @endif> {{ $reportRow['images_before'] }}</td>
                            @else
                                <td>N/A</td>
                            @endif

                            @if (array_key_exists('images_after', $reportRow))
                                <td @if($reportRow['images_after'] == 'No') style="color: red; font-weight: bold;" @endif> {{ $reportRow['images_after'] }}</td>
                            @else
                                <td>N/A</td>
                            @endif

                            @if (array_key_exists('measurement_before', $reportRow))
                                <td @if($reportRow['measurement_before'] == 'No') style="color: red; font-weight: bold;" @endif> {{ $reportRow['measurement_before'] }}</td>
                            @else
                                <td>N/A</td>
                            @endif

                            @if (array_key_exists('measurement_after', $reportRow))
                                <td @if($reportRow['measurement_after'] == 'No') style="color: red; font-weight: bold;" @endif> {{ $reportRow['measurement_after'] }}</td>
                            @else
                                <td>N/A</td>
                            @endif
                            <td @if($reportRow['invoice'] == 'No') style="color: red; font-weight: bold;" @endif>{{ $reportRow['invoice'] }}</td>
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
                </tbody>
            </table>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>