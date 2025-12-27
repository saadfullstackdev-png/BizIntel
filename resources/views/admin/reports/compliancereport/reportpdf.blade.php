@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>

    <style>
        .date {
            text-align: right;
        }

        .logo {
            width: 200px;
            text-align: left;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            margin-top: 30px;
        }

        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td, th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }

        .No{
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}"
                                 class="img-responsive" alt=""/>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding-left: 450px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Compliance Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table">
        <tr style="background: #364150; color: #fff;">
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
            @foreach($reportData as $reportRow)
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

</body>
</html>