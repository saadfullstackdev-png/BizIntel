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
            <h1>{{ 'Actual Revenue Report' }}</h1>
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
        <?php $count = 1; ?>
        <table class="table">
            <tr>
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
                                    <td style="text-align: right;font-weight: bold;">
                                        <?php
                                        $dateservicetotal += $reportservice['total'];
                                        echo number_format($reportservice['total'], 2);
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
                                        $centertotal += $dateservicetotal;
                                        echo number_format($dateservicetotal, 2);
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
                                    $regiontotal += $centertotal;
                                    echo number_format($centertotal, 2);
                                    ?>
                                </b>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold">Region Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right"><b>
                                <?php
                                $grandtotal += $regiontotal;
                                echo number_format($regiontotal, 2);
                                ?>
                            </b>
                        </td>
                    </tr>
                @endforeach
                <tr style="background: #364150; color: #fff;">
                    <td style="font-weight: bold; color: #fff;">Grand Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: bold;text-align: right; color: #fff;"><b>
                            <?php
                            echo number_format($grandtotal, 2);
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