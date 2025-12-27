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
            <h1>{{ 'Incentive Detail Report'  }}</h1>
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
                    <th>@lang('global.leads.fields.full_name')</th>
                    <th>@lang('global.leads.fields.email')</th>
                    <th>@lang('global.leads.fields.phone')</th>
                    <th>@lang('global.leads.fields.gender')</th>
                    <th>@lang('global.leads.fields.role')</th>
                    <th>@lang('global.leads.fields.region')</th>
                    <th>@lang('global.leads.fields.city')</th>
                    <th>@lang('global.leads.fields.location')</th>
                    <th>@lang('global.leads.fields.total_Revenue')</th>
                    <th>@lang('global.leads.fields.commission')</th>
                    <th>@lang('global.leads.fields.incentive')</th>
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $total = 0; $rtotal = 0;?>
                    @foreach($reportData as $user)
                        <tr>
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['phone'] }}</td>
                            <td>{{ $user['gender'] }}</td>
                            <td>{{ $user['Role'] }}</td>
                            <td>{{ $user['Region'] }}</td>
                            <td>{{ $user['City'] }}</td>
                            <td>{{ $user['Location'] }}</td>
                            <td>
                                <?php
                                $rtotal += $user['TotalRevenue'];
                                echo number_format($user['TotalRevenue'], 2)
                                ?>
                            </td>
                            <td>{{$user['commission']}}</td>
                            <td style="text-align: right;color: #fff;">
                                <?php
                                $total += $user['Incentive'];
                                echo number_format($user['Incentive'], 2);
                                ?>
                            </td>
                        </tr>
                        <tr >
                            <th>Invoice No.</th>
                            <th>Service</th>
                            <th>Payment Date</th>
                            <th>Created by</th>
                            <th>Patient</th>
                            <th>Service Price</th>
                            <th>Discount Name</th>
                            <th>Discount Type</th>
                            <th>Discount Amount</th>
                            <th>Invoice Price</th>
                            <th>Centre</th>
                        </tr>
                        <?php  $grandserviceprice = 0;$grandtotalservice = 0;?>
                        @foreach($user['detail'] as $reportRow)
                            <tr>
                                <td style="text-align: center;">{{ $reportRow['id']}}</td>
                                <td>{{ (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->name : '-' }}</td>
                                <td>{{ ($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow['created_at'], null)->format('h:i A') : '-' }}</td>
                                <td>{{ (array_key_exists($reportRow['created_by'], $filters['users'])) ? $filters['users'][$reportRow['created_by']]->name : '-' }}</td>
                                <td>{{ (array_key_exists($reportRow['patient_id'], $filters['patients'])) ? $filters['patients'][$reportRow['patient_id']]->name : '-' }}</td>
                                <td style="text-align: right;">
                                    <?php
                                    $grandserviceprice += (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '';
                                    echo number_format((array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '', 2);
                                    ?>
                                </td>
                                <td>{{ (array_key_exists($reportRow['discount_id'], $filters['discounts'])) ? $filters['discounts'][$reportRow['discount_id']]->name : '-' }}</td>
                                <td>{{$reportRow['discount_type']==null?'-':$reportRow['discount_type']}}</td>
                                <td style="text-align: right;">{{$reportRow['discount_price']==null?'-':$reportRow['discount_price']}}</td>
                                <td style="text-align: right;">
                                    <?php
                                    $grandtotalservice += $reportRow['total_price'];
                                    echo number_format($reportRow['total_price'], 2);
                                    ?>
                                </td>
                                <td>{{ (array_key_exists($reportRow['location_id'], $filters['locations'])) ? $filters['locations'][$reportRow['location_id']]->name : '-' }}</td>
                            </tr>
                        @endforeach
                        <tr class="sh-docblue">
                            <td style="text-align: center;">Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;"><?php echo number_format($grandserviceprice, 2);?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right;"><?php echo number_format($grandtotalservice, 2);?></td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr style="background: #364150; color: #fff ;">
                        <td style=" color: #fff ;"><b>Grand Total</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="text-align: right;color: #fff;"><b>
                                <?php
                                echo number_format($rtotal, 2);
                                ?>
                            </b>
                        </td>
                        <td></td>
                        <td style="text-align: right;color: #fff;"><b>
                                <?php
                                echo number_format($total, 2);
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
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>
</div>