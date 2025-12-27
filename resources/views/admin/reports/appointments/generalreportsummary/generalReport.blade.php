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
            <h1>{{ 'GENERAL REPORT SUMMARY'  }}</h1>
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
        </div><!-- End sn-table-head -->
        <div class="table-wrapper" id="topscroll">
            <table class="table">
                @if(count($reportData2))
                    <?php $Gtotalcount = 0;?>
                    @foreach($reportData2 as $key => $reporttype)
                        <tr class="shdoc-header">
                            @if ( $key === config('constants.appointment_type_consultancy'))

                                <th>Consultancy Name</th>


                            @elseif( $key === config('constants.appointment_type_service') )

                                <th>Treatment Name</th>


                            @endif
                            <th>Count</th>
                        </tr>
                        <?php $totalcount = 0;?>
                        @foreach($reporttype as $reportcount)
                            <tr>
                                <td>{{$reportcount['name']}}</td>
                                <td>{{number_format($reportcount['count'])}}</td>
                                <?php
                                $totalcount += $reportcount['count'];
                                $Gtotalcount += $reportcount['count']
                                ?>
                            </tr>
                        @endforeach
                        @if( $key === config('constants.appointment_type_consultancy'))
                            <tr class="sh-docblue">
                                <td><label style="font-weight: bold;"> {{ config('constants.Consultancy') }}</label>
                                </td>
                                <td style="font-weight: bold;">{{number_format($totalcount)}}</td>
                            </tr>
                        @elseif ( $key === config('constants.appointment_type_service'))
                            <tr class="sh-docblue">
                                <td><label style="font-weight: bold;"> {{ config('constants.Service') }}</label></td>
                                <td style="font-weight: bold;">{{number_format($totalcount)}}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr style="background: #364150;">
                        <td><label style="font-weight: bold;color: #fff;"> Grand Total</label></td>
                        <td style="font-weight: bold;color: #fff;">{{number_format($Gtotalcount)}}</td>
                    </tr>
                @else
                    @if($message)
                        <tr>
                            <td colspan="12" align="center">{{$message}}</td>
                        </tr>
                    @endif
                @endif
            </table>
        </div>
    </div>
</div>
<div class="clear clearfix"></div>
<!-- Liabilities and Assets -->
<script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></script>