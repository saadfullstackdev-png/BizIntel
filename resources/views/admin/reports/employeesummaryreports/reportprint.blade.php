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
            <h1>{{ 'Employee Appointment Summary Report'  }}</h1>
        </div>
    </div>
</div>

<div class="panel-body sn-table-body">
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


    <div class="table-wrapper">
    <table class="table">
        <tr>
            <th>Created By</th>
            <th>Total Appointments</th>
        </tr>
        @if(count($reportData))
            <?php $count = 0;?>
            @foreach($reportData as $reportpackagedata)
                @foreach($reportpackagedata['records'] as $reportRow )
                    <?php $created_by = (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '';
                    $count ++;
                    ?>
                @endforeach
                <tr>
                    <td>{{$created_by}}</td>
                    <td>{{$count}}</td>
                </tr>
                <?php $count = 0;?>
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
</div>
</body>
</html>