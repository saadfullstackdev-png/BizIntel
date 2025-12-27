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
            <h1>{{ 'List of Clients who claimed refunds Day Base Against Plans Report'  }}</h1>
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
                            <td>For the month
                                of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
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
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Centre</th>
                        <th>Refund Note</th>
                        <th>Amount</th>
                        <th>Created At</th>
                    </thead>
                    <tbody>
                    @if(count($reportData))
                        <?php $grefund = 0; ?>
                        @foreach($reportData as $reportpackagedata)
                            <tr style="background-color: #dddddd">
                                <td>{{$reportpackagedata['name']}}</td>
                                <td style="text-align: right"><?php echo number_format($reportpackagedata['total_price'],2)?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $trefund = 0;?>
                            @foreach($reportpackagedata['refunds'] as $reportRow )
                                <tr>
                                    <td>{{$reportRow['patient_id']}}</td>
                                    <td>{{$reportRow['patient']}}</td>
                                    <td>{{$reportRow['location']}}</td>
                                    <td>{{$reportRow['refund_note']}}</td>
                                    <td style="text-align: right"><?php
                                        $trefund+= $reportRow['cash_amount'];
                                        echo number_format($reportRow['cash_amount'],2);
                                        ?></td>
                                    <td>{{($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y')  : '-'}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>Refund Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align: right" ><?php
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
                            <td style="color: white;text-align: right "><?php echo number_format($grefund,2)?></td>
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>