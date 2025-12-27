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
                        <td>Actual Revenue Report</td>
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
            <th width="10%">Region</th>
            <th width="23%">Centre</th>
            <th>Date</th>
            <th>Service Name</th>
            <th>Revenue</th>
        </tr>
        @if(count($reportdata))
            <?php $grandtotal = 0;?>
            @foreach($reportdata as $reportregion)
                <tr>
                    <td>{{$reportregion['name']}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $regiontotal = 0;?>
                @foreach($reportregion['centers'] as $reportcentre )
                    <tr>
                        <td></td>
                        <td>{{$reportcentre['name']}}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php $centertotal = 0;?>
                    @foreach($reportcentre['date'] as $reportday)
                        <tr>
                            <td></td>
                            <td></td>
                            <td>{{($reportday['Date']) ? \Carbon\Carbon::parse($reportday['Date'], null)->format('M j, Y')  : '-'}}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php $dateservicetotal = 0;?>
                        @foreach($reportday['service'] as $reportservice)
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{(array_key_exists($reportservice['service_id'], $filters['services'])) ? $filters['services'][$reportservice['service_id']]->name : ''}}</td>
                                <td style="text-align: right;!important;font-weight: bold;">
                                    <?php
                                    $dateservicetotal += $reportservice['total'];
                                    echo number_format($reportservice['total'],2);
                                    ?>
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold;">Day Total</td>
                            <td style="font-weight: bold;text-align: right"><b>
                                    <?php
                                    $centertotal+=$dateservicetotal;
                                    echo number_format($dateservicetotal,2);
                                    ?>
                                </b>
                            </td>
                        </tr>
                    @endforeach
                    <tr style="background: #35a1d4;color: #fff;">
                        <td></td>
                        <td style="font-weight: bold;color: #fff;">Centre Total</td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;text-align: right;color: #fff;"><b>
                                <?php
                                $regiontotal+=$centertotal;
                                echo number_format($centertotal,2);
                                ?>
                            </b>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td style="font-weight: bold;">Region Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right"><b>
                            <?php
                            $grandtotal+=$regiontotal;
                            echo number_format($regiontotal,2);
                            ?>
                        </b>
                    </td>
                </tr>
            @endforeach
            <tr style="background: #364150; color: #fff;">
                <td style="font-weight: bold;">Grand Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-weight: bold;text-align: right"><b>
                        <?php
                        echo number_format($grandtotal,2);
                        ?>
                    </b>
                </td>
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