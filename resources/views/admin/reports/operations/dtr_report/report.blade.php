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
            <h1>{{ 'DTR Report' }}</h1>
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
                            <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
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
                <th>Centre</th>
                <th>Region</th>
                <th>City</th>
                <th>Doctor</th>
                <th>Service</th>
                <th>Target Service</th>
                <th>Target Service Completed</th>
                <th>Ratio</th>
                <th>Remaining Days</th>
                </thead>
                <tbody>
                @if(count($reportData))
                    <?php $g_target_service = 0; $g_target_service_complete = 0; ?>
                    @foreach($reportData as $reportlocationdata)
                        <tr style="background-color: #364150; color:white">
                            <td>{{$reportlocationdata['location']}}</td>
                            <td>{{$reportlocationdata['region']}}</td>
                            <td>{{$reportlocationdata['city']}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php $target_service = 0; $target_service_complete = 0; ?>
                        @foreach($reportlocationdata['doctors'] as $reportRow )
                            <tr>
                                <?php
                                $target_service+=$reportRow['target_service_count'];
                                $target_service_complete+=$reportRow['target_service_done'];
                                $g_target_service+=$reportRow['target_service_count'];
                                $g_target_service_complete+=$reportRow['target_service_done'];
                                ?>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>{{$reportRow['doctor']}}</td>
                                <td>{{$reportRow['service']}}</td>
                                <td>{{$reportRow['target_service_count']}}</td>
                                <td>{{$reportRow['target_service_done']}}</td>
                                <td>{{number_format($reportRow['target_complete_ratio'],1).'%'}}</td>
                                <td>{{$reportRow['remaining_day']}}</td>
                            </tr>
                        @endforeach
                        <tr style="color: #37abdc !important;font-weight: bold;">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            <td>{{$target_service}}</td>
                            <td>{{$target_service_complete}}</td>
                            <td>{{number_format(($target_service_complete/$target_service)*100,1).'%'}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    <tr style="color: #37abdc !important;font-weight: bold;">
                        <td>Grand Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{$g_target_service}}</td>
                        <td>{{$g_target_service_complete}}</td>
                        <td>{{number_format(($g_target_service_complete/$g_target_service)*100,1).'%'}}</td>
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