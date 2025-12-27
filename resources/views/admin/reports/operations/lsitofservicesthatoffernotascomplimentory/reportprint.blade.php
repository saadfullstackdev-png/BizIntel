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
            <h1>{{ 'List of Services that can not be offered as Complimentary Report' }}</h1>
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
                    <td>For the month
                        of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
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
            <th>ID</th>
            <th>Name</th>
            <th>Duration</th>
            <th>Complimentory</th>
            <th>Price</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{ $reportRow['id'] }}</td>
                    <td>{{$reportRow['name']}}</td>
                    <td>{{ $reportRow['duration'] . ' mins' }}</td>
                    <td>{{$reportRow['complimentory'] == '1'?'Yes':'NO'}}</td>
                    <td>{{number_format($reportRow['price'], 2)}}</td>
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