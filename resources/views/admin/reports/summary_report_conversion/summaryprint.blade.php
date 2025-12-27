@inject('request', 'Illuminate\Http\Request')
<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        body {
            margin: 10mm;
            font-family: Arial, sans-serif;
        }
        .date {
            text-align: right;
        }
        .logo {
            width: 200px;
            text-align: left;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #dddddd;
            padding: 8px;
            font-size: 12px;
            text-align: center;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: bold;
        }
        .region-header {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .central-region { background-color: #ff9900 !important; }
        .north-region { background-color: #0066cc !important; }
        .south-region { background-color: #ffff00 !important; }
        .grand-total {
            background-color: #d3d3d3;
            font-weight: bold;
        }
        .header-info {
            color: #364150;
        }
        .shdoc-header {
            background-color: #364150;
            color: #ffffff;
        }
    </style>
</head>
<body>
<div class="report-summary">
    <table>
        <tr>
            <td><img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" alt="Logo"></td>
            <td style="text-align: right;">
                <table>
                    <tr>
                        <td style="width: 80px;">Report:</td>
                        <td>Bookings Arrivals & Conversions Report</td>
                    </tr>
                    <tr>
                        <td style="width: 80px;">Duration:</td>
                        <td>From <strong>{{ $start_date }}</strong> to <strong>{{ $end_date }}</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 80px;">Date:</td>
                        <td><strong>{{ \Carbon\Carbon::now()->format('Y-m-d H:i A') }}</strong></td>
                    </tr>
                    @if($request->get('patient_id'))
                    <tr>
                        <td style="width: 80px;">Patient:</td>
                        <td><strong>{{ $patientName ?? 'N/A' }}</strong></td>
                    </tr>
                    @endif
                    @if($request->get('doctor_id'))
                    <tr>
                        <td style="width: 80px;">Doctor:</td>
                        <td><strong>{{ $doctorName ?? 'N/A' }}</strong></td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr class="shdoc-header">
                <th></th>
                <th colspan="8">Bookings</th>
                <th colspan="13">Arrivals</th>
                <th colspan="2">Conversions</th>
            </tr>
            <tr class="shdoc-header">
                <th>Region / Center</th>
                <th>Booking Quota</th>
                <th>Body Contouring</th>
                <th>HIFU Facelift</th>
                <th>Trilogy Ice</th>
                <th>Facials</th>
                <th>Others</th>
                <th>Booked</th>
                <th>Booked Ratio (%)</th>
                <th>Body Contouring Arrived</th>
                <th>Body Contouring %</th>
                <th>HIFU Facelift Arrived</th>
                <th>Trilogy Ice Arrived</th>
                <th>Facials Arrived</th>
                <th>Others Arrived</th>
                <th>Arrived</th>
                <th>Walk-Ins</th>
                <th>Arrived Call Center</th>
                <th>Walkins % in Arrivals</th>
                <th>Ratio of Walkins to Booked Arrivals</th>
                <th>Arrival %age Booked</th>
                <th>Arrival %age Total</th>
                @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                    <th>Converted</th>
                @endif
                <th>Converted Ratio (%)</th>
            </tr>
        </thead>
        <tbody>
            @if(count($summaryData) > 0)
                @foreach($summaryData as $regionData)
                    <tr class="region-header {{ strtolower(str_replace(' ', '-', $regionData['region'])) }}-region">
                        <td colspan="{{ 23 + ($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted' ? 1 : 0) }}">{{ $regionData['region'] }}</td>
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
                            <td>{{ $data['booked_body_contouring'] > 0 ? number_format(($data['arrived_body_contouring'] / $data['booked_body_contouring']) * 100, 2) : 'N/A' }}%</td>
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
                            @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                                <td>{{ $data['converted'] }}</td>
                            @endif
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
                        <td>{{ $regionData['subtotals']['booked_body_contouring'] > 0 ? number_format(($regionData['subtotals']['arrived_body_contouring'] / $regionData['subtotals']['booked_body_contouring']) * 100, 2) : 'N/A' }}%</td>
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
                        @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                            <td>{{ $regionData['subtotals']['converted'] }}</td>
                        @endif
                        <td>{{ $regionData['subtotals']['conversion_ratio'] }}%</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ 22 + ($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted' ? 1 : 0) }}" align="center">No records found.</td>
                </tr>
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
                <td>{{ $grandTotals['booked_body_contouring'] > 0 ? number_format(($grandTotals['arrived_body_contouring'] / $grandTotals['booked_body_contouring']) * 100, 2) : 'N/A' }}%</td>
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
                @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                    <td>{{ $grandTotals['converted'] }}</td>
                @endif
                <td>{{ $grandTotals['conversion_ratio'] }}%</td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>