@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
    @if($request->get('medium_type') == 'pdf')
        @include('partials.pdf_head')
    @else
        @include('partials.head')
    @endif
    <style type="text/css">
        @page {
            margin: 10px 20px;
        }
        @media print {
            table {
                font-size: 12px;
            }
            .tr-root-group {
                background-color: #F3F3F3;
                color: rgba(0, 0, 0, 0.98);
                font-weight: bold;
            }
            .tr-group {
                font-weight: bold;
            }
            .bold-text {
                font-weight: bold;
            }
            .error-text {
                font-weight: bold;
                color: #FF0000;
            }
            .ok-text {
                color: #006400;
            }
        }
    </style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Actual Revenue Report' }}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('print');">
                    <i class="fa fa-print"></i><span>Print</span>
                </a>
            @endif
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
    <div class="bordered">
        <div class="sn-table-head">
            <div class="row">
                <div class="col-md-2">
                    <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
                </div>
                <div class="col-md-6">&nbsp;</div>
                <div class="col-md-4">
                    <table class="dark-th-table table table-bordered">
                        <tr>
                            <th width="25%">Duration</th>
                            <td>From {{ $start_date }} to {{ $end_date }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="table-wrapper" id="topscroll">
            <table class="table">
                <thead>
                <th width="10%">Region</th>
                <th width="23%">Centre</th>
                <th>Date</th>
                <th>Service Name</th>
                <th>Revenue</th>
                </thead>
                <tbody>
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
                            <td><b>Region Total</b></td>
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
                        <td style="font-weight: bold; color: #fff;">Grand Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;text-align: right; color: #fff;"><b>
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
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>