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
            <h1>{{ 'Staff Wise Revenue Report' }}</h1>
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
    <div class="table-wrapper" id="topscroll">
        <table class="table">
            <tr>
                <th>Centre</th>
                <th>City</th>
                <th>Region</th>
                <th>Doctor</th>
                <th>Created At</th>
                <th>Revenue In</th>
                <th>Refund/Out</th>
                <th>In Hand Revenue</th>
            </tr>
            @if(count($report_data))
                <?php $grandtotal = 0; ?>
                @foreach($report_data as $reportlocation)
                    <tr>
                        <td><b>{{$reportlocation['centre']}}</b></td>
                        <td><b>{{$reportlocation['city']}}</b></td>
                        <td><b>{{$reportlocation['region']}}</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php $centre_revenue_total = 0; $centre_refund_total = 0; $centre_total = 0;?>
                    @foreach($reportlocation['doctor_info'] as $reportdoctor )
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold">{{$reportdoctor['doctor']}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php $doctor_revenue_total = 0; $doctor_refund_total = 0; $doctor_total = 0;?>
                        @foreach($reportdoctor['doctor_revenue'] as $reportrevenue)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{($reportrevenue['created_at']) ? \Carbon\Carbon::parse($reportrevenue['created_at'], null)->format('M j, Y')  : ''}}</td>
                                <td>
                                    {{$reportrevenue['revenue']?number_format($reportrevenue['revenue'],2):''}}
                                </td>
                                <td>
                                    {{$reportrevenue['refund_out']?number_format($reportrevenue['refund_out'],2):''}}
                                </td>
                                <td></td>
                                <?php
                                $doctor_revenue_total += $reportrevenue['revenue'] ? $reportrevenue['revenue'] : 0;
                                $doctor_refund_total += $reportrevenue['refund_out'] ? $reportrevenue['refund_out'] : 0;
                                $centre_revenue_total += $reportrevenue['revenue'] ? $reportrevenue['revenue'] : 0;
                                $centre_refund_total += $reportrevenue['refund_out'] ? $reportrevenue['refund_out'] : 0;

                                ?>
                            </tr>
                        @endforeach
                        <?php $doctor_total = $doctor_revenue_total - $doctor_refund_total; ?>
                        <tr style="background-color: #35a1d4;color: #fff">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><label style="font-weight: bold; color: #fff;">Total</label></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold;color: #fff">{{$doctor_total?number_format($doctor_total,2):0}}</td>
                        </tr>
                    @endforeach
                    <?php $centre_total = $centre_revenue_total - $centre_refund_total; ?>
                    <tr style="background: #364150; color: #fff;">
                        <td><label style="font-weight: bold;color: #fff;">Total</label></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;color: #fff;">{{$centre_total?number_format($centre_total,2):0}}</td>
                        <?php $grandtotal += $centre_total ? $centre_total : 0?>
                    </tr>
                @endforeach
                <tr style="background: #364150; color: #fff;">
                    <td><label style="font-weight: bold;color: #fff;">Grand Total</label></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold;color: #fff;">{{$grandtotal?number_format($grandtotal,2):0}}</td>
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