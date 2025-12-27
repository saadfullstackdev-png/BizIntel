<!DOCTYPE html>
<html>
<head>
    <style>
        .date {
            text-align: right;
        }
        .logo {
            width: 200px;
            text-align: left;
        }
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            margin-top: 30px;
        }
        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        td, th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }
        .table td, .table th {
            text-align: left;
            padding: 8px;
            font-size: 12px;
        }
         table.table tr td{
            padding: 12px;
        }
        table.table tr:first-child{
            background-color: #fff;
        }
        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td >
                                <img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" class="img-responsive" alt=""/>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding-left: 450px;">
                    <table style="float: right;">
            <tr>
                <td style="width: 70px;">Name</td>
                <td>Staff Report</td>
            </tr>
            <tr>
                <td style="width: 70px;">Duration</td>
                <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong></td>
            </tr>
            <tr>
                <td style="width: 70px;">Date</td>
                <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
            </tr>
        </table>
                </td>
            </tr>
        </table>
    <?php $count=1; ?>
    <table class="table">
        <tr>
            <th>#</th>
            <th>@lang('global.staff.fields.full_name')</th>
            <th>@lang('global.staff.fields.email')</th>
            <th>@lang('global.staff.fields.gender')</th>
            <th>@lang('global.staff.fields.centre')</th>
            <th>@lang('global.staff.fields.city')</th>
            <th>@lang('global.staff.fields.region')</th>
            <th>@lang('global.staff.fields.phone')</th>
        </tr>
        @if(count($staff))
            @foreach($staff as $thisStaff)
                <tr>
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
                <td colspan="5" align="center">No record round.</td>
            </tr>
        @endif
    </table>
</div>
</div>

</body>
</html>