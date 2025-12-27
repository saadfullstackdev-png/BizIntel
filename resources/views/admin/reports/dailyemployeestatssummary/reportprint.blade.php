@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    {{--<style>--}}
        {{--.date {--}}
            {{--text-align: right;--}}
        {{--}--}}

        {{--.logo {--}}
            {{--width: 200px;--}}
            {{--text-align: left;--}}
        {{--}--}}

        {{--table {--}}
            {{--font-family: arial, sans-serif;--}}
            {{--border-collapse: collapse;--}}
            {{--width: 100%;--}}
            {{--margin-top: 30px;--}}
        {{--}--}}

        {{--.table th {--}}
            {{--border: 1px solid #dddddd;--}}
            {{--text-align: left;--}}
            {{--padding: 8px;--}}
        {{--}--}}

        {{--td, th {--}}
            {{--text-align: left;--}}
            {{--font-size: 12px;--}}
            {{--padding:8px;--}}
        {{--}--}}

        {{--.table td, .table th {--}}
            {{--text-align: left;--}}
            {{--padding: 5px;--}}
            {{--font-size: 12px;--}}
        {{--}--}}
        {{--table.table tr td{--}}
            {{--padding: 12px;--}}
        {{--}--}}
        {{--table.table tr:first-child{--}}
            {{--background-color: #fff;--}}
        {{--}--}}
        {{--.table tr:nth-child(odd) {--}}
            {{--background-color: #dddddd;--}}
        {{--}--}}
    {{--</style>--}}
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'SALE SUMMARY SERVICE WISE' }}</h1>
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
            <th>Service</th>
            <th>Total</th>
        </tr>
        @if(count($reportData))
            <?php $grand = 0; ?>
            @foreach($reportData as $reportData)
                <tr>
                    <td>{{$reportData['name']}}</td>
                    <td><?php
                        $grand +=$reportData['amount'];
                        echo number_format($reportData['amount'],2);
                        ?></td>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff; font-weight: bold;">
                <td>Grand Total</td>
                <td><?php echo number_format($grand,2); ?></td>
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