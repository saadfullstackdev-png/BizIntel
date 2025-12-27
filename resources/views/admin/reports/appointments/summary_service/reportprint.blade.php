<!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/generic-style.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="sn-table-holder">
        <div class="sn-report-head">
            <div class="sn-title">
                <h1>{{ 'Appointments Summary By Service Report' }}</h1>
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
        <table class="table">
                <thead>
                    <tr>
                        <th width="10%">Region</th>
                        <th width="25%">Centre</th>
                        <th>Service</th>
                        <th style="text-align:right; padding-right:20px;">Total Appointments</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($reportData))
                        @php($grand_total = 0)
                        @foreach($reportData as $region)
                            @if(count($region['centres']))
                                <tr>
                                    <td colspan="4" style="font-weight: bold">{{ $region['name'] }}</td>
                                </tr>
                                @php($region_appointments = 0)
                                @foreach($region['centres'] as $centre)
                                    <tr>
                                        <td></td>
                                        <td colspan="3" style="font-weight: bold">{{ $centre['name'] }}</td>
                                    </tr>
                                    @php($centre_appointments = 0)
                                    @if(count($centre['services']))
                                        @foreach($centre['services'] as $service)
                                            @php($centre_appointments = $centre_appointments + $service['total_appointments'])
                                            <tr>
                                                <td colspan="2"></td>
                                                <td>{{ $service['name'] }}</td>
                                                <td style="text-align:right; padding-right:20px;">{{ number_format($service['total_appointments']) }}</td>
                                            </tr>
                                        @endforeach
                                        @php($region_appointments = $region_appointments + $centre_appointments)
                                        <tr>
                                            <td colspan="3" style="text-align: right;">Total for {{ $centre['name'] }}</td>
                                            <td style="text-align:right; padding-right:20px;">{{ number_format($centre_appointments) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                @php($grand_total = $grand_total + $region_appointments)
                                <tr style="background-color: #37abdc; color: #fff;">
                                    <td colspan="3" style="text-align: right;color: #fff">Total for {{ $region['name'] }}</td>
                                    <td style="text-align:right; padding-right:20px;color: #fff">{{ number_format($region_appointments) }}</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <th colspan="3" style="text-align: right;">Total for All Regions</th>
                            <th style="text-align:right; padding-right:20px;">{{ number_format($grand_total) }}</th>
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
</body>
</html>
