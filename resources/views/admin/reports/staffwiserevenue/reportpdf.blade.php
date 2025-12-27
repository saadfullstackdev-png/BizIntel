@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf {
            width: 100%;
        }

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

        .table {
            width: 100%;
        }

        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td, th {
            text-align: left;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px 5px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
    </style>
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>
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
                        <td>Staff Wise Revenue Report</td>
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
        <tr class="shdoc-header">
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