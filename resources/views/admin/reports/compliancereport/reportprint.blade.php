<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Compliance Report' }}</h1>
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
    <div id="" class="table-wrapper" style="overflow-x: auto;">
        <table class="table">
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
            @if(count($reportData))
                @foreach( $reportData as $reportRow )
                    <tr>
                        <td> {{ $reportRow['id'] }}</td>
                        <td> {{ $reportRow['client'] }}</td>
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
        </table>
    </div>
</div>
<script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</body>
</html>