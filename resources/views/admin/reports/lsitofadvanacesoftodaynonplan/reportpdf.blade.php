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
            padding: 8px;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }

        table.table tr td {
            padding: 12px;
        }

        table.table tr:first-child {
            background-color: #fff;
        }

        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
        .shdoc-header{
            background: #364150; color: #fff;
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
                        <td>List Of Advances As Of Today For Non Plan Report</td>
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
        <tr style="background: #364150; color: #fff;">
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Email</th>
            <th>Scdeuled</th>
            <th>Doctor</th>
            <th>City</th>
            <th>Center</th>
            <th>Total Price</th>
            <th>Advance</th>
            <th>Outstanding</th>
            <th>Used Balance</th>
            <th>Unused Balance</th>
        </tr>
        @if(count($reportData))
            <?php $gtotal = 0; $gadvances = 0; $goutstanding = 0; $guse = 0; $gunused = 0; ?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td>{{$reportRow['patient_id']}}</td>
                    <td>{{$reportRow['patient_name']}}</td>
                    <td>{{$reportRow['email']}}</td>
                    <td>{{$reportRow['schedule']}}</td>
                    <td>{{$reportRow['doctor']}}</td>
                    <td>{{$reportRow['city']}}</td>
                    <td>{{$reportRow['location']}}</td>
                    <td><?php echo number_format($reportRow['total_price'],2)?></td>
                    <td><?php echo number_format($reportRow['advancebalance'],2) ?></td>
                    <td><?php echo number_format($reportRow['outstandingbalance'],2) ?></td>
                    <td><?php echo number_format($reportRow['usedbalance'],2) ?></td>
                    <td><?php echo number_format($reportRow['unusedbalance'],2) ?></td>
                    <?php
                    $gtotal+=$reportRow['total_price'];
                    $gadvances+=$reportRow['advancebalance'];
                    $goutstanding+=$reportRow['outstandingbalance'];
                    $guse+=$reportRow['usedbalance'];
                    $gunused+=$reportRow['unusedbalance'];
                    ?>

                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;font-weight: bold">
                <td style="text-align: center;font-weight: bold;color: #fff;">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo number_format($gtotal,2);?></td>
                <td><?php echo number_format($gadvances,2);?></td>
                <td><?php echo number_format($goutstanding,2);?></td>
                <td><?php echo number_format($guse,2);?></td>
                <td><?php echo number_format($gunused,2);?></td>
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