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
        .table tr:first-child {
            background-color: #fff;
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
            <td style="padding-left: 100px;">
                <table style="float: right;">
                    <tr>
                        <td style="width: 70px;">Name</td>
                        <td>Client With Birthday + x Days Report</td>
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
        <tr style="background: #364150; color: #fff;">
            <th>#</th>
            <th>@lang('global.leads.fields.patientid')</th>
            <th>@lang('global.leads.fields.full_name')</th>
            <th>@lang('global.leads.fields.cnic')</th>
            <th>@lang('global.leads.fields.dob')</th>
            <th>@lang('global.leads.fields.email')</th>
            <th>@lang('global.leads.fields.gender')</th>
            <th>@lang('global.leads.fields.region')</th>
            <th>@lang('global.leads.fields.city')</th>
            <th>@lang('global.leads.fields.lead_status')</th>
            <th>@lang('global.leads.fields.service')</th>
            <th>@lang('global.leads.fields.created_by')</th>
        </tr>
        @if(count($leads))
            @foreach($leads as $leads)
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{ $leads->patient_id }}</td>
                    <td>{{ $leads->name }}</td>
                    <td>{{ $leads->cnic }}</td>
                    <td>{{ $leads->dob }}</td>
                    <td>{{ $leads->email }}</td>
                    <td><?php if($leads->gender == '1'){
                            echo 'Male';
                        } else {
                            echo 'Female';
                        }?></td>
                    <td>{{$region[$leads->region_id]->name}}</td>
                    <td>{{ $Cities[$leads->city_id]->name }}</td>
                    <td>{{ $lead_status[$leads->lead_status_id]->name }}</td>
                    <td>{{ $services[$leads->service_id]->name }}</td>
                    <td>{{ $users[$leads->created_by]->name }}</td>
                </tr>
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
    </table>
</div>
</div>

</body>
</html>