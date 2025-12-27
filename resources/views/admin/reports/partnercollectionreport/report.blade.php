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
            <h1>{{ 'Partner Collection Report' }}</h1>
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
                        <th>Cash Flow</th>
                        <th>Amount</th>
                        <th>Tax</th>
                        <th>Net Amount</th>
                        <th>Refund/Cash Out</th>
                        <th>Balance</th>
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $machineamount_in_g = 0; $machinetax_in_g = 0; $machinenet_in_g = 0; $machinetotal_out_g = 0;  ?>
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
                        </tr>
                        <?php $machineamount_in_t = 0; $machinetax_in_t = 0; $machinenet_in_t = 0; $machinetotal_out_t = 0;  ?>
                        @foreach($reportlocation['machine'] as $reportmachine )
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight: bold">{{ $reportmachine['name'] }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $machineamount_in = 0; $machinetax_in = 0; $machinenet_in= 0; $machinetotal_out = 0;  ?>
                            @foreach($reportmachine['transaction'] as $paymentrecord)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>{{$paymentrecord['name']}}</td>
                                    <td>{{$paymentrecord['flow']}}</td>

                                    <td>{{$paymentrecord['amount']?number_format($paymentrecord['amount'],2):''}}</td>
                                    <td>{{$paymentrecord['tax']?number_format($paymentrecord['tax'],2):''}}</td>
                                    <td>{{$paymentrecord['net_amount']?number_format($paymentrecord['net_amount'],2):''}}</td>

                                    <td>{{$paymentrecord['amount_out']?number_format($paymentrecord['amount_out'],2):''}}</td>
                                    <td></td>
                                    @php
                                        $machineamount_in+=$paymentrecord['amount']?$paymentrecord['amount']:0;
                                        $machinetax_in+=$paymentrecord['tax']?$paymentrecord['tax']:0;
                                        $machinenet_in+=$paymentrecord['net_amount']?$paymentrecord['net_amount']:0;
                                        $machinetotal_out+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                                        $machineamount_in_t+=$paymentrecord['amount']?$paymentrecord['amount']:0;
                                        $machinetax_in_t+=$paymentrecord['tax']?$paymentrecord['tax']:0;
                                        $machinenet_in_t+=$paymentrecord['net_amount']?$paymentrecord['net_amount']:0;
                                        $machinetotal_out_t+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                                        $machineamount_in_g+=$paymentrecord['amount']?$paymentrecord['amount']:0;
                                        $machinetax_in_g+=$paymentrecord['tax']?$paymentrecord['tax']:0;
                                        $machinenet_in_g+=$paymentrecord['net_amount']?$paymentrecord['net_amount']:0;
                                        $machinetotal_out_g+=$paymentrecord['amount_out']?$paymentrecord['amount_out']:0;

                                    @endphp
                                </tr>
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="font-weight: bold;">Total</td>
                                <td></td>
                                <td></td>
                                <td style="font-weight: bold;">{{number_format($machineamount_in,2)}}</td>
                                <td style="font-weight: bold;">{{number_format($machinetax_in,2)}}</td>
                                <td style="font-weight: bold;">{{number_format($machinenet_in,2)}}</td>

                                <td style="font-weight: bold;">{{number_format($machinetotal_out,2)}}</td>
                                <td style="font-weight: bold;">{{number_format($machineamount_in-$machinetotal_out,2)}}</td>
                            </tr>
                        @endforeach
                        <tr style="background-color: #35a1d4; color: #fff;">
                            <td style="font-weight: bold;color: #fff">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="font-weight: bold;color: #fff">{{number_format($machineamount_in_t,2)}}</td>
                            <td style="font-weight: bold;color: #fff">{{number_format($machinetax_in_t,2)}}</td>
                            <td style="font-weight: bold;color: #fff">{{number_format($machinenet_in_t,2)}}</td>
                            <td style="font-weight: bold;color: #fff">{{number_format($machinetotal_out_t,2) }}</td>
                            <td style="font-weight: bold;color: #fff">{{number_format($machineamount_in_t-$machinetotal_out_t,2)}}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #364150; color: #fff;">
                        <td colspan="3" style="font-weight:bold; color: #fff;">Grand Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight:bold; color: #fff;">{{number_format($machineamount_in_g,2)}}</td>
                        <td style="font-weight:bold; color: #fff;">{{number_format($machinetax_in_g,2)}}</td>
                        <td style="font-weight:bold; color: #fff;">{{number_format($machinenet_in_g,2)}}</td>
                        <td style="font-weight:bold; color: #fff;">{{number_format($machinetotal_out_g,2) }}</td>
                        <td style="font-weight:bold; color: #fff;">{{number_format($machineamount_in_g-$machinetotal_out_g,2)}}</td>
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