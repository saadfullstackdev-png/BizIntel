@inject('request', 'Illuminate\Http\Request')
<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Highest Paid Client Report' }}</h1>
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
                    <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</strong>
                    </td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
    <table class="table">
        <tr>
            <th width="15%">ID</th>
            <th>Client Name</th>
            <th>Email</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Revenue</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $reportlocationdata)
                <tr>
                    <td><?php echo $reportlocationdata['name']; ?></td>
                    <td><?php echo $reportlocationdata['region']; ?></td>
                    <td><?php echo $reportlocationdata['city']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($reportlocationdata['clients'] as $reportRow )
                    <tr>
                        <td>{{$reportRow['id']}}</td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                                <a target="_blank"
                                   href="{{ route('admin.patients.preview',[$reportRow['id']]) }}">{{ $reportRow['name']}}</a>
                            @else
                                {{ $reportRow['name']}}
                            @endif
                        </td>
                        <td>{{$reportRow['email']}}</td>
                        <td>{{$reportRow['gender']}}</td>
                        <td>{{$reportRow['dob']}}</td>
                        <td style="text-align: right"><?php echo number_format($reportRow['Revenue'], 2); ?></td>
                    </tr>
                @endforeach
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