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
            <h1>{{ $reportName }}</h1>
        </div>
        <div class="sn-buttons">

                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i><span>Excel</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i><span>PDF</span>
                </a>
                <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('print');">
                    <i class="fa fa-print"></i><span>Print</span>
                </a>

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
                <div class="col-md-6">
                </div>
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
                {{--<th>#</th>--}}
                <th>@lang('global.staff.fields.full_name')</th>
                <th>@lang('global.staff.fields.email')</th>
                <th>@lang('global.staff.fields.gender')</th>
                <th>@lang('global.staff.fields.centre')</th>
                <th>@lang('global.staff.fields.city')</th>
                <th>@lang('global.staff.fields.region')</th>
                <th>@lang('global.staff.fields.phone')</th>
                {{--<th>@lang('global.staff.fields.service')</th>--}}
                </thead>
                <tbody>
<?php
                $count = 1;
?>
                @if(count($staffData))
                    @foreach($staffData as $thisStaff)
                        {{-- dd($thisStaff[key($thisStaff)]) --}}
                        <?php  $count = 1; ?>
                        <tr style="background-color: #dddddd;">
                            <td>{{ $regionNames[$loop->index] }}</td>
                            <td>{{-- $thisStaff[key($thisStaff)]['city'] --}}</td>
                            <td>{{-- $thisStaff[key($thisStaff)]['region'] --}}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        @foreach($thisStaff as $user)
                        <tr>
                            {{--<td>{{$count++}}</td>--}}
                            <td>{{ $user['name'] }}</td>
                            <td>{{ $user['email'] }}</td>
                            <td>{{ $user['gender'] }}</td>
                            <td>
                                {{ $user['centre_name'] }}
                            </td>
                            <td> {{ $user['city'] }}</td>
                            <td>{{ $user['region'] }}
                            </td>
                            <td>{{ $user['phone'] }}</td>
                            {{--<td>{{ $services[$thisStaff->service_id]->name }}</td>--}}

                        </tr>
                            @endforeach
                        <tr class="sh-docblue">
                            <td style="color: #fff;">{{ $regionNames[$loop->index] }}</td>
                            <td style="color: #fff;">Total</td>
                            <td style="color: #fff;">{{ count($thisStaff) }}</td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                            <td style="color: #fff;"></td>
                        </tr>
                    @endforeach
                    <tr class="shdoc-header">
                        <td style="color: #fff;">Grand Total</td>
                        <td style="color: #fff;">{{ $totalRecords }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
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
