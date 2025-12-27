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
            <h1>{{ 'List of Clients who claimed refunds Day Base Against Non Plans Report'  }}</h1>
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
                    <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y') }}</strong></td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>
    <table class="table">
        <tr style="background: #364150; color: #fff;">
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>email</th>
            <th>Appointment Scheduled</th>
            <th>Service</th>
            <th>Doctor</th>
            <th>City</th>
            <th>Centre</th>
            <th>Refund Note</th>
            <th>Amount</th>
            <th>Created At</th>
        </tr>
        @if(count($reportData))
            <?php $grefund = 0; ?>
            @foreach($reportData as $reportappointmentdata)
                <tr >
                    <td>{{$reportappointmentdata['name']}}</td>
                    <td style="text-align: right"><?php echo number_format($reportappointmentdata['total_price'],2)?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $trefund = 0;?>
                @foreach($reportappointmentdata['refunds'] as $reportRow )
                    <tr>
                        <td>{{$reportRow['patient_id']}}</td>
                        <td>{{$reportRow['patient_name']}}</td>
                        <td>{{$reportRow['email']}}</td>
                        <td>{{$reportRow['schedule']}}</td>
                        <td>{{$reportRow['service']}}</td>
                        <td>{{$reportRow['doctor']}}</td>
                        <td>{{$reportRow['city']}}</td>
                        <td>{{$reportRow['location']}}</td>
                        <td>{{$reportRow['refund_note']}}</td>
                        <td style="text-align: right"><?php
                            $trefund+= $reportRow['cash_amount'];
                            echo number_format($reportRow['cash_amount'],2);
                            ?></td>
                        <td>{{($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y')  : '-'}}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #3aaddc">
                    <td>Refund Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right"><?php
                        $grefund+=$trefund;
                        echo number_format($trefund,2);
                        ?></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;">
                <td style="color: white">Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="color: white;text-align: right"><?php echo number_format($grefund,2)?></td>
                <td></td>
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