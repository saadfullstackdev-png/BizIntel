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
                color: #fff;
                background-color: #364150;
            }
        }
    </style>
@endif

<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Pabau Record Revenue Report'  }}</h1>
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
                    <thead bg-default>
                        <th>Center</th>
                        <th>Region</th>
                        <th>City</th>
                        <th>Client</th>
{{--                        <th>Phone</th>--}}
                        <th>Invoice No.</th>
                        <th>Issue Date</th>
                        <th>Total Amount</th>
                        <th>Paid Amount</th>
                        <th>Outstanding Amount</th>
                        <th>Amount</th>
                        <th>Date</th>
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $grantotal = 0; ?>
                    @foreach($reportData as $reportlocation)
                        <tr>
                            <td style="font-weight: bold"><?php echo $reportlocation['name']; ?></td>
                            <td style="font-weight: bold"><?php echo $reportlocation['region']; ?></td>
                            <td style="font-weight: bold"><?php echo $reportlocation['city']; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
{{--                            <td></td>--}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php $centotal = 0; ?>
                        @foreach($reportlocation['pabau_rocord'] as $reportuser )
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{ $reportuser['name'] }}</td>
{{--                                <td>{{ $reportuser['phone'] }}</td>--}}
                                <td>{{ $reportuser['invoice_no'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($reportuser['issue_date'])->format('M j, Y H:i A') }}</td>
                                <td>{{ number_format($reportuser['total_amount']) }}</td>
                                <td>{{ number_format($reportuser['paid_amount'])}}</td>
                                <td>{{ number_format($reportuser['outstanding_amount']) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $sumtotal = 0; ?>
                            @foreach($reportuser['pabau_record_payment'] as $paymentrecord)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
{{--                                    <td></td>--}}
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ number_format($paymentrecord['amount'])}}</td>
                                    <td>{{ \Carbon\Carbon::parse($paymentrecord['Date'])->format('M j, Y H:i A') }}</td>
                                    <?php $sumtotal+=$paymentrecord['amount']; ?>
                                    <?php $centotal+=$paymentrecord['amount']; ?>
                                    <?php $grantotal+=$paymentrecord['amount']; ?>
                                </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
{{--                                <td></td>--}}
                                <td></td>
                                <td></td>
                                <td style="font-weight: bold">Total</td>
                                <td style="font-weight: bold">{{number_format($sumtotal) }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #35a1d4; color: #fff;">
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold">Total</td>
                            <td></td>
{{--                            <td></td>--}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold">{{number_format($centotal) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr style="background: #364150; color: #fff;">
                        <td colspan="3" align="right" style="font-weight: bold;color: #fff;">Grand Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
{{--                        <td></td>--}}
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;color: #fff;">{{number_format($grantotal) }}</td>
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