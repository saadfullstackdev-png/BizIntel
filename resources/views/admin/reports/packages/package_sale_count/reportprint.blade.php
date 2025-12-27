@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet"
          type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Package Sale Count' }}</h1>
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
            <th>Location</th>
            <th>Package</th>
            <th>Count</th>
        </tr>
        @if(count($reportData))
            <?php $totalCount = 0;?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{$reportRow['location']}}</td>
                    <td>{{$reportRow['package']}}</td>
                    <td style="text-align: right;">{{ number_format( $reportRow['count'], 2) }}</td>
                </tr>
                <?php
                    $totalCount += $reportRow['count'];
                ?>
            @endforeach

            <tr style="background: #364150;color: #fff;">
                <td style="font-weight: bold">Total</td>
                <td></td>
                <td style="text-align: right;font-weight: bold">{{ number_format( $totalCount, 2) }}</td>
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
    </table>
</div>
</div>

</body>
</html>