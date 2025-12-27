@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <link href="{{ url('metronic/assets/global/css/print-page.css') }}" rel="stylesheet" type="text/css"/>
    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css"/>

</head>
<body>
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>{{ 'Center Performance Stats By Revenue Report' }}</h1>
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
        <tr>
            <th>ID</th>
            <th>Client Name</th>
            <th>Created At</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Email</th>
            <th>Scheduled</th>
            <th>City</th>
            <th>Centre</th>
            <th>Status</th>
            <th>Type</th>
            <th>Service Price</th>
            <th>Invoiced Price</th>
            <th>Created By</th>
        </tr>
        @if(count($reportData))
            <?php $count = 0;$salesgrandtotal = 0; $servicegrandtotal = 0; $grandcount = 0  ?>
            @foreach($reportData as $reportpackagedata)
                <tr>
                    <td><?php echo $reportpackagedata['name']; ?></td>
                    <td><?php echo $reportpackagedata['region']; ?></td>
                    <td><?php echo $reportpackagedata['city']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $count = 0;$salestotal = 0; $servicetotal = 0;?>
                @foreach($reportpackagedata['records'] as $reportRow )
                    <tr>
                        <td> {{ $reportRow->patient_id }} </td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                                <a target="_blank"
                                   href="{{ route('admin.patients.preview',[$reportRow->patient->id]) }}">{{ $reportRow->patient->name }}</a>
                            @else
                                {{ $reportRow->patient->name }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reportRow->created_at)->format('M j, Y H:i A') }}</td>
                        <td>{{ (array_key_exists($reportRow->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportRow->doctor_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                        <td>{{ $reportRow->patient->email }}</td>
                        <td>{{ ($reportRow->scheduled_date) ? \Carbon\Carbon::parse($reportRow->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow->scheduled_time, null)->format('h:i A') : '-' }}</td>
                        <td>{{ (array_key_exists($reportRow->city_id, $filters['cities'])) ? $filters['cities'][$reportRow->city_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportRow->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportRow->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportRow->appointment_type_id]->name : '' }}</td>
                        <td style="text-align: right;"><?php
                            $serviceprice = (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
                            $servicetotal += $serviceprice;
                            echo number_format($serviceprice, 2);
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <?php
                            $salestotal += $reportRow->Salestotal;
                            echo number_format($reportRow->Salestotal, 2);
                            ?>
                        </td>
                        <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                    </tr>
                    <?php $count++; $grandcount++; ?>
                @endforeach
                <tr class="sh-docblue">
                    <td><?php echo $reportpackagedata['name']; ?></td>
                    <td>Total</td>
                    <td>{{$count}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;">
                        <?php
                        $servicegrandtotal += $servicetotal;
                        echo number_format($servicetotal,2);
                        ?>
                    </td>
                    <td style="text-align: right;">
                        <?php
                        $salesgrandtotal += $salestotal;
                        echo number_format($salestotal,2);
                        ?>
                    </td>
                    <td></td>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff; font-weight: bold">
                <td></td>
                <td>Grand Total</td>
                <td>{{$grandcount}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><?php echo number_format($servicegrandtotal,2); ?></td>
                <td style="text-align: right;"><?php echo number_format($salesgrandtotal,2); ?></td>
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