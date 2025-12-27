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


<div class="panel-body pad table-responsive">
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div>
                <h3>Name: Staff Report</h3>
            </div>
            <div class="actions">
                <a class="btn btn-circle btn-default" href="javascript:;" onclick="FormControls.printReport('excel');">
                    <i class="fa fa-file-excel-o"></i>&nbsp;Excel
                </a>
                <a class="btn btn-circle btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                    <i class="fa fa-file-pdf-o"></i>&nbsp;PDF
                </a>
                <a class="btn btn-circle btn-default" href="javascript:;" onclick="FormControls.printReport('print');">
                    <i class="fa fa-print"></i>&nbsp;Print
                </a>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row margin-bottom-25">
                <div class="col-md-2">
                    <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
                </div>
                <div class="col-md-6">
                </div>
                <div class="col-md-4">
                    <table class="table table-bordered table-striped table-condensed flip-content">
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

            <table class="table">
                <thead>
                <th>#</th>
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
                <?php $count = 1; ?>
                @if(count($staff))
                    @foreach($staff as $thisStaff)
                        <tr>
                            {{-- dd($thisStaff) --}}
                            {{-- count($thisStaff->user_has_locations) --}}
                            {{-- count($thisStaff->doctorhaslocation) --}}
{{--                            @if(count($thisStaff->user_has_locations) > 0))

                            @endif
                            @if(count($thisStaff->doctorhaslocation) > 0))
                            --}}{{--  dd($thisStaff->doctorhaslocation[0]) --}}{{--
                            @endif--}}
                            {{-- dd($locations) --}}
                            {{-- dd($thisStaff->user_has_locations->location_id) --}}
                            {{-- dd($thisStaff->doctorhaslocation->location_id) --}}
                            <td>{{$count++}}</td>
                            <td>{{ $thisStaff->name }}</td>
                            <td>{{ $thisStaff->email }}</td>
                            <td><?php if ($thisStaff->gender == '1') {
                                    echo 'Male';
                                } else {
                                    echo 'Female';
                                }?></td>
                            <td>
                                @if(count($thisStaff->doctorhaslocation) > 0)
                                    {{ $filters['locations'][$thisStaff->doctorhaslocation[0]->location_id]->name }}
                                {{-- (array_key_exists($thisStaff->doctorhaslocation[0]->location_id, $filters['locations'])) ? $filters['locations'][$thisStaff->doctorhaslocation[0]->location_id]->name : '' --}}
                                @endif
                                @if(count($thisStaff->user_has_locations) > 0))
                                {{  $thisStaff->user_has_locations[0]->location_id }}
                                @endif
                            </td>
                            <td>{{ $filters['locations'][$thisStaff->doctorhaslocation[0]->location_id]->city->name }}</td>
                            <td>
                                @if(count($thisStaff->doctorhaslocation) > 0)
                                    {{  $thisStaff->doctorhaslocation[0]->location->region->name }}
                                @endif
                                @if(count($thisStaff->user_has_locations) > 0)
                                    {{  $thisStaff->user_has_locations[0]->location->region->name }}
                                @endif
                            </td>
                            <td>{{ $thisStaff->phone }}</td>
                            {{--<td>{{ $services[$thisStaff->service_id]->name }}</td>--}}

                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12" align="center">No record round.</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
</div>