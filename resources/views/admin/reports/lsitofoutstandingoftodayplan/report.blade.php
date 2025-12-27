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
            .shdoc-header{
                background: #364150;
                color: #fff;
            }
        }
    </style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'List Of Outstanding As Of Today For Plan Report'  }}</h1>
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
        </div><!-- End sn-table-head -->
        <div class="table-wrapper" id="topscroll">
            <table class="table">
                <thead>
                <th>ID</th>
                <th>Patient Name</th>
                <th>plan Name</th>
                <th>Is Refund</th>
                <th>Centre</th>
                <th>Total Price</th>
                <th>Advances</th>
                <th>Outstanding</th>
                <th>Use Balance</th>
                <th>Unused Balance</th>
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $gtotal = 0; $gadvances = 0; $goutstanding = 0; $guse = 0; $gunused = 0; ?>
                    @foreach($reportData as $reportRow)
                        <tr>
                            <td>{{$reportRow['patient_id']}}</td>
                            <td>{{$reportRow['patient']}}</td>
                            <td>{{$reportRow['name']}}</td>
                            <td>{{$reportRow['is_refund']}}</td>
                            <td>{{$reportRow['location']}}</td>
                            <td  style="text-align: right;"><?php echo number_format($reportRow['total_price'],2)?></td>
                            <td  style="text-align: right;"><?php echo number_format($reportRow['advancebalance'],2) ?></td>
                            <td  style="text-align: right;"><?php echo number_format($reportRow['outstandingbalance'],2) ?></td>
                            <td  style="text-align: right;"><?php echo number_format($reportRow['usedbalance'],2) ?></td>
                            <td  style="text-align: right;"><?php echo number_format($reportRow['unusedbalance'],2) ?></td>
                            <?php
                            $gtotal+=$reportRow['total_price'];
                            $gadvances+=$reportRow['advancebalance'];
                            $goutstanding+=$reportRow['outstandingbalance'];
                            $guse+=$reportRow['usedbalance'];
                            $gunused+=$reportRow['unusedbalance'];
                            ?>
                        </tr>
                    @endforeach
                    <tr style="background: #364150; color: #fff;">
                        <td style="text-align: center;color: #fff;font-weight: bold">Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right; color: #fff;font-weight: bold"><?php echo number_format($gtotal,2);?></td>
                        <td style="text-align: right; color: #fff;font-weight: bold"><?php echo number_format($gadvances,2);?></td>
                        <td style="text-align: right; color: #fff;font-weight: bold"><?php echo number_format($goutstanding,2);?></td>
                        <td style="text-align: right; color: #fff;font-weight: bold"><?php echo number_format($guse,2);?></td>
                        <td style="text-align: right; color: #fff;font-weight: bold"><?php echo number_format($gunused,2);?></td>
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