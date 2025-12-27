@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
    @if($request->get('medium_type') == 'pdf')
        @include('partials.pdf_head')
    @else
        @include('partials.head')
    @endif
    <style type="text/css">
        @page { margin: 10px 20px; }
        @media print {
            table { font-size: 12px; }
            .region-header { background-color: #f2f2f2; font-weight: bold}
            .grand-total { background-color: #d3d3d3; font-weight: bold; }
            .central-region { background-color: #ff9900 !important; } /* Orange */
            .north-region { background-color: #0066cc !important; } /* Blue */
            .south-region { background-color: #ffff00 !important; } /* Yellow */
        }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .table th { background-color: #f8f8f8; }
        .table-wrapper .table tbody > tr > td {
            padding: 8px 15px;
            vertical-align: middle;
            text-align: center !important;
            font-size: 14px;
            min-width: 150px;
            color: #364150;
        }
    </style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>Bookings Arrivals & Conversions Report</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printconversionReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printconversionReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printconversionReport('print');">
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
                        <tr><th width="25%">Duration</th><td>From {{ $start_date }} to {{ $end_date }}</td></tr>
                        <tr><th>Date</th><td>{{ \Carbon\Carbon::now()->format('Y-m-d H:i A') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="table-wrapper" id="topscroll">
            <table class="table toogle">
                <thead>
                    <tr>
                        <th></th>
                        <th colspan="8">Bookings</th>
                        <th colspan="13">Arrivals</th>
                        <th colspan="2">Conversions</th>
                    </tr>
                    <tr>
                        <th>Region / Center</th>
                        <th>Booking Quota</th>
                        <th>Body Contouring</th>
                        <th>HIFU Facelift</th>
                        <th>Trilogy Ice</th>
                        <th>Facials</th>
                        <th>Others</th>
                        <th>Booked</th>
                        <th>Booked Ratio</th>
                        <th>Body Contouring</th>
                        <th>Persentage</th>
                        <th>HIFU Facelift</th>
                        <th>Trilogy Ice</th>
                        <th>Facials</th>
                        <th>Others</th>
                        <th>Arrived</th>
                        <th>Walk-ins</th>
                        <th>Arrived Call Center</th>
                        <th>%Walkins in Arrivals</th>
                        <th>Ratio of Walkins to Booked Arrivals</th>
                        <th>Arrival %age Booked</th>
                        <th>Arrival %age Total</th>
                        <th>Converted</th>
                        <th>Converted Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($summaryData) > 0)
                        @foreach($summaryData as $regionData)
                            <tr class="region-header {{ strtolower(str_replace(' ', '-', $regionData['region'])) }}">
                                <td class="text-center" colspan="24">{{ $regionData['region'] }}</td>
                            </tr>
                            @foreach($regionData['centers'] as $data)
                                <tr>
                                    <td>{{ $data['center'] }}</td>
                                    <td>{{ $data['booking_quota'] }}</td>
                                    <td>{{ $data['booked_body_contouring'] }}</td>
                                    <td>{{ $data['booked_hifu_facelift'] }}</td>
                                    <td>{{ $data['booked_trilogy_ice'] }}</td>
                                    <td>{{ $data['booked_facials'] }}</td>
                                    <td>{{ $data['booked_others'] }}</td>
                                    <td>{{ $data['booked'] }}</td>
                                    <td>{{ $data['booking_ratio'] }}%</td>
                                    <td>{{ $data['arrived_body_contouring'] }}</td>
                                    <td>{{ $data['booked_body_contouring'] > 0 ? number_format(($data['arrived_body_contouring'] / $data['booked_body_contouring']) * 100, 2) : 'N/A' }}</td>
                                    <td>{{ $data['arrived_hifu_facelift'] }}</td>
                                    <td>{{ $data['arrived_trilogy_ice'] }}</td>
                                    <td>{{ $data['arrived_facials'] }}</td>
                                    <td>{{ $data['arrived_others'] }}</td>
                                    <td>{{ $data['arrived'] }}</td>
                                    <td>{{ $data['walk_ins'] }}</td>
                                    <td>{{ $data['arrived_call_center'] }}</td>
                                    <td>{{ $data['walkins_in_arrivals'] }}%</td>
                                    <td>{{ $data['ratio_walkins_to_booked'] }}%</td>
                                    <td>{{ $data['arrival_booked'] }}%</td>
                                    <td>{{ $data['arrival_total'] }}%</td>
                                    <td>{{ $data['converted'] }}</td>
                                    <td>{{ $data['conversion_ratio'] }}%</td>
                                </tr>
                            @endforeach
                            <tr class="region-header {{ strtolower(str_replace(' ', '-', $regionData['region'])) }}-region">
                                <td><strong>Subtotal {{ $regionData['region'] }}</strong></td>
                                <td>{{ $regionData['subtotals']['booking_quota'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_body_contouring'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_hifu_facelift'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_trilogy_ice'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_facials'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_others'] }}</td>
                                <td>{{ $regionData['subtotals']['booked'] }}</td>
                                <td>{{ $regionData['subtotals']['booking_ratio'] }}%</td>
                                <td>{{ $regionData['subtotals']['arrived_body_contouring'] }}</td>
                                <td>{{ $regionData['subtotals']['booked_body_contouring'] > 0 ? number_format(($regionData['subtotals']['arrived_body_contouring'] * 100 / $regionData['subtotals']['booked_body_contouring']), 2) . '%' : '0%' }} </td>
                                <td>{{ $regionData['subtotals']['arrived_hifu_facelift'] }}</td>
                                <td>{{ $regionData['subtotals']['arrived_trilogy_ice'] }}</td>
                                <td>{{ $regionData['subtotals']['arrived_facials'] }}</td>
                                <td>{{ $regionData['subtotals']['arrived_others'] }}</td>
                                <td>{{ $regionData['subtotals']['arrived'] }}</td>
                                <td>{{ $regionData['subtotals']['walk_ins'] }}</td>
                                <td>{{ $regionData['subtotals']['arrived_call_center'] }}</td>
                                <td>{{ $regionData['subtotals']['walkins_in_arrivals'] }}%</td>
                                <td>{{ $regionData['subtotals']['ratio_walkins_to_booked'] }}%</td>
                                <td>{{ $regionData['subtotals']['arrival_booked'] }}%</td>
                                <td>{{ $regionData['subtotals']['arrival_total'] }}%</td>
                                <td>{{ $regionData['subtotals']['converted'] }}</td>
                                <td>{{ $regionData['subtotals']['conversion_ratio'] }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="24" align="center">No records found.</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="grand-total">
                        <td><strong>Grand Total</strong></td>
                        <td>{{ $grandTotals['booking_quota'] }}</td>
                        <td>{{ $grandTotals['booked_body_contouring'] }}</td>
                        <td>{{ $grandTotals['booked_hifu_facelift'] }}</td>
                        <td>{{ $grandTotals['booked_trilogy_ice'] }}</td>
                        <td>{{ $grandTotals['booked_facials'] }}</td>
                        <td>{{ $grandTotals['booked_others'] }}</td>
                        <td>{{ $grandTotals['booked'] }}</td>
                        <td>{{ $grandTotals['booking_ratio'] }}%</td>
                        <td>{{ $grandTotals['arrived_body_contouring'] }}</td>
                        <td>{{ $grandTotals['booked_body_contouring'] > 0 ? number_format(($grandTotals['arrived_body_contouring'] * 100 / $grandTotals['booked_body_contouring']), 2) . '%' : '0%' }} </td>
                        <td>{{ $grandTotals['arrived_hifu_facelift'] }}</td>
                        <td>{{ $grandTotals['arrived_trilogy_ice'] }}</td>
                        <td>{{ $grandTotals['arrived_facials'] }}</td>
                        <td>{{ $grandTotals['arrived_others'] }}</td>
                        <td>{{ $grandTotals['arrived'] }}</td>
                        <td>{{ $grandTotals['walk_ins'] }}</td>
                        <td>{{ $grandTotals['arrived_call_center'] }}</td>
                        <td>{{ $grandTotals['walkins_in_arrivals'] }}%</td>
                        <td>{{ $grandTotals['ratio_walkins_to_booked'] }}%</td>
                        <td>{{ $grandTotals['arrival_booked'] }}%</td>
                        <td>{{ $grandTotals['arrival_total'] }}%</td>
                        <td>{{ $grandTotals['converted'] }}</td>
                        <td>{{ $grandTotals['conversion_ratio'] }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>