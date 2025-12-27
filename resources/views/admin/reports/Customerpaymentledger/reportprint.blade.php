@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Customer Payment Ledger Report' }}</h1>
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

    <table class="table">
        <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>Centre</th>
            <th>Transaction Type</th>
            <th>Cash In</th>
            <th>Cash Out</th>
            <th>Balance</th>
            <th>Created At</th>
        </tr>
        @if(count($reportData))
            <?php $grandserviceprice = 0; $grandtotalservice = 0; ?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td> {{ $reportRow['patient_id'] }}</td>
                    <td>{{$reportRow['patient']}}</td>
                    <td>{{$reportRow['phone']}}</td>
                    <td>{{$reportRow['centre']}}</td>
                    <td>{{$reportRow['transtype']}}</td>
                    <td>{{$reportRow['cash_in']}}</td>
                    <td>{{$reportRow['cash_out']}}</td>
                    <td>{{$reportRow['balance']}}</td>
                    <td>{{\Carbon\Carbon::parse($reportRow['created_at'])->format('F j,Y h:i A')}}</td>
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