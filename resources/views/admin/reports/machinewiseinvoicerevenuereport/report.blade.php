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
            <h1>{{ 'Machine Wise Invoice Revenue Report' }}</h1>
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
                    <th>Center</th>
                    <th>Region</th>
                    <th>City</th>
                    <th>Machine</th>
                    <th>Client</th>
                    <th>Service Price</th>
                    <th>Discount Name</th>
                    <th>Discount Type</th>
                    <th>Discount Price</th>
                    <th>Amount</th>
                    <th>Tax Value</th>
                    <th>Net Amount</th>
                    <th>Created At</th>
                    <th>Is Exclusive</th>
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
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $centotal = 0; ?>
                            @foreach($reportlocation['machine'] as $reportmachine )
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ $reportmachine['name'] }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php $machinetotal = 0; ?>
                                @foreach($reportmachine['machine_array'] as $paymentrecord)
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>{{$paymentrecord['client']}}</td>
                                        <td>{{number_format($paymentrecord['service_price'],2)}}</td>
                                        <td>{{$paymentrecord['discount_name']}}</td>
                                        <td>{{$paymentrecord['discount_type']}}</td>
                                        <td>{{number_format($paymentrecord['discount_price'],2)}}</td>
                                        <td>{{number_format($paymentrecord['amount'],2)}}</td>
                                        <td>{{number_format($paymentrecord['tax_value'],2)}}</td>
                                        <td>{{number_format($paymentrecord['net_amount'],2)}}</td>
                                        <td>{{ \Carbon\Carbon::parse($paymentrecord['created_at'])->format('M j, Y H:i A') }}</td>
                                        <td>{{ $paymentrecord['is_exclusive']?'Yes':'NO' }}</td>

                                        <?php $machinetotal+=$paymentrecord['net_amount']; ?>
                                        <?php $centotal+=$paymentrecord['net_amount']; ?>
                                        <?php $grantotal+=$paymentrecord['net_amount']; ?>
                                    </tr>
                                @endforeach
                                <tr class="sh">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="font-weight: bold">Total</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="font-weight: bold">{{number_format($machinetotal) }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endforeach
                            <tr class="sh-docblue">
                                <td style="font-weight: bold;color: #fff">Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="color: #fff;">{{number_format($centotal) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #364150; color: #fff;">
                            <td colspan="3"  style="font-weight: bold;color:#fff">Grand Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold;color:#fff">{{number_format($grantotal) }}</td>
                            <td></td>
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