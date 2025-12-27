<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ $reportName }}</h1>
        </div>
    </div>
</div>
<div class="invoice-pdf">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80" alt=""/>
        </div>
        <div class="print-time">
            <table class="dark-th-table table table-bordered">
                <tr>
                    <th width="25%">Duration</th>
                    <td>From {{ $start_date }} to {{ $end_date }}</td>
                </tr>
                <tr>
                    <th>Date</th>
                    <td>{{ Carbon\Carbon::now()->format('Y-m-d') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <?php $count=1; ?>
    <table class="table">
        <tr>
            {{--<th>#</th>--}}
            <th>@lang('global.staff.fields.full_name')</th>
            <th>@lang('global.staff.fields.email')</th>
            <th>@lang('global.staff.fields.gender')</th>
            <th>@lang('global.staff.fields.centre')</th>
            <th>@lang('global.staff.fields.city')</th>
            <th>@lang('global.staff.fields.region')</th>
            <th>@lang('global.staff.fields.phone')</th>
        </tr>
        @if(count($staffData))
            @foreach($staffData as $thisStaff)
                <?php  $count = 1; ?>
                    <tr>
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
                    <tr style="background-color: #37abdc;color: #fff;font-weight: bold;">
                        <td style="color: #fff;">{{ $regionNames[$loop->index] }}</td>
                        <td style="color: #fff;">Total</td>
                        <td style="color: #fff;">{{ count($thisStaff) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
            @endforeach
                <tr style="background: #364150;color: #fff;font-weight: bold;">
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
    </table>
</div>
</div>

</body>
</html>