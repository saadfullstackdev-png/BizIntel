@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>

    <style>

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
        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        td, th {
            text-align: left;
            font-size: 12px;
            padding:8px;
        }
        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }
        table.table tr td{
            padding: 12px;
        }
        table.table tr:first-child{
            background-color: #fff;
        }
        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
        .shdoc-header{
            background: #364150;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td >
                                <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" class="img-responsive" alt=""/>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding-left: 450px;">
                    <table style="float: right;">
            <tr>
                <td style="width: 70px;">Name</td>
                <td>Sale Summary Service Wise</td>
            </tr>
            <tr>
                <td style="width: 70px;">Duration</td>
                <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong></td>
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
        <tr style="background: #364150; color: #fff;">
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
            <tr style="background: #364150;color: #fff; font-weight: bold">
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