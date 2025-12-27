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
            <h1>{{'Consume Plan Revenue'}}</h1>
        </div>
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;"
                   onclick="FormControls.printReport('print');">
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
                    <th>Plan ID</th>
                    <th>Service</th>
                    <th>Center</th>
                    <th>Service Price</th>
                    <th>Discount Name</th>
                    <th>Discount Type</th>
                    <th>Discount Amount</th>
                    <th>Amount</th>
                    <th>Tax</th>
                    <th>Tax Value</th>
                    <th>Total Amount</th>
                    <th>Is Exclusive</th>
                    </thead>
                    <tbody>
                    @php $amount_t = 0; $tax_price_t = 0; $total_amount_t = 0; @endphp
                    @if(count($reportData))
                        @foreach($reportData as $reportRow)
                            <tr>
                                <td>{{$reportRow['plan_id']}}</td>
                                <td>{{$reportRow['service']}}</td>
                                <td>{{$reportRow['location']}}</td>
                                <td>{{number_format($reportRow['service_price'])}}</td>
                                <td>{{$reportRow['disocunt_name']?$reportRow['disocunt_name']:'-'}}</td>
                                <td>{{$reportRow['discount_type']?$reportRow['discount_type']:'-'}}</td>
                                <td>{{$reportRow['discount_amount']?number_format($reportRow['discount_amount']):'-'}}</td>
                                <td style="text-align: right">{{number_format($reportRow['amount'])}}</td>
                                <td>{{$reportRow['tax'].'%'}}</td>
                                <td style="text-align: right">{{$reportRow['is_exclusive'] == 1?number_format($reportRow['tax_value']):number_format($reportRow['tax_amount']-$reportRow['amount'])}}</td>
                                <td style="text-align: right">{{number_format($reportRow['tax_amount'])}}</td>
                                <td>{{$reportRow['is_exclusive']==1?'Yes':'No'}}</td>
                                @php
                                    $amount_t += $reportRow['amount'];
                                    $tax_price_t += $reportRow['is_exclusive'] == 1?$reportRow['tax_value']:$reportRow['tax_amount']-$reportRow['amount'];
                                    $total_amount_t += $reportRow['tax_amount'];
                                @endphp
                            </tr>
                        @endforeach
                        <tr style="background: #364150;color: #fff; font-weight: bold">
                            <td style="text-align: center; color: #fff;">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;color: #fff;">{{ number_format( $amount_t) }}</td>
                            <td></td>
                            <td style="text-align: right;color: #fff;">{{ number_format( $tax_price_t) }}</td>
                            <td style="text-align: right;color: #fff;"> {{ number_format( $total_amount_t) }} </td>
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