@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Marketing Report'  }}</h1>
        </div>
    </div>
</div>

<div class="panel-body sn-table-body">
    <div class="sn-table-head">
        <div class="print-logo">
            <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
        </div>
        <div class="print-time">
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
    <?php $count=1; ?>
    <table class="table">
        <tr style="color: #fff;background-color: #364150;">
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
            <th>@lang('global.leads.fields.referred_by')</th>
        </tr>
        @if(count($leads))
            @foreach($leads as $leads)
                <tr>
                    <td>{{ $leads->patient_id }}</td>
                    <td>{{ $leads->name }}</td>
                    <td>{{ $leads->cnic }}</td>
                    <td>{{ $leads->dob }}</td>
                    <td>{{ $leads->email }}</td>
                    <td><?php if ($leads->gender == '1') {
                            echo 'Male';
                        } else if($leads->gender == '2') {
                            echo 'Female';
                        }?></td>
                    <td>{{$leads->region_id?$region[$leads->region_id]->name:''}}</td>
                    <td>{{$leads->city_id?$Cities[$leads->city_id]->name:''}}</td>
                    <td>{{ $lead_status[$leads->lead_status_id]->name }}</td>
                    <td>{{ $services[$leads->service_id]->name }}</td>
                    <td>{{ $users[$leads->created_by]->name }}</td>
                    <td>{{$leads->referred_by?$users[$leads->referred_by]->name:''}}</td>
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