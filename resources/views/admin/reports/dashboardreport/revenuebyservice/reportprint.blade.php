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
            <h1>{{($performance == 'true')?'My Revenue By Services':'Revenue By Services'}}</h1>
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
        </div>
        <div class="print-time">
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
    <table class="table">
        <thead>
        <th>Service</th>
        <th>Total</th>
        </thead>
        <tbody>
        @if(count($reportData))
            <?php $grand = 0; ?>
            @foreach($reportData as $reportData)
                <tr>
                    <td>{{$reportData['name']}}</td>
                    <td style="text-align: right">{{number_format($reportData['amount'],2)}}</td>
                    <?php $grand += $reportData['amount']; ?>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff;">
                <td>Grand Total</td>
                <td style="text-align: right;">{{number_format($grand,2)}}</td>
            </tr>
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
</div>

</body>
</html>