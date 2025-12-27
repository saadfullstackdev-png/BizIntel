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
            <h1>{{ 'Highest Paid Client Report'  }}</h1>
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
                    <thead class="bg-default">
                        <tr>
                            <th width="15%">ID</th>
                            <th>Client Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>DOB</th>
                            <th>Revenue</th>
                        </tr>
                </thead>
                <tbody>
                @if(count($reportData))
                    @foreach($reportData as $reportlocationdata)
                        <tr style="background-color: #dddddd">
                            <td><?php echo $reportlocationdata['name']; ?></td>
                            <td><?php echo $reportlocationdata['region']; ?></td>
                            <td><?php echo $reportlocationdata['city']; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @foreach($reportlocationdata['clients'] as $reportRow )
                            <tr>
                                <td>{{$reportRow['id']}}</td>
                                <td>
                                    @if($request->get('medium_type') == 'web')
                                        <a target="_blank"
                                           href="{{ route('admin.patients.preview',[$reportRow['id']]) }}">{{ $reportRow['name']}}</a>
                                    @else
                                        {{ $reportRow['name']}}
                                    @endif
                                </td>
                                <td>{{$reportRow['email']}}</td>
                                <td>{{$reportRow['gender']}}</td>
                                <td>{{$reportRow['dob']}}</td>
                                <td style="text-align: right"><?php echo number_format($reportRow['Revenue'], 2); ?></td>
                            </tr>
                        @endforeach
                    @endforeach
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