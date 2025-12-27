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
            <h1>{{ 'Client With Completed Treatment Report' }}</h1>
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
    <div class="table-wrapper" id="topscroll">
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
            <th>Created by</th>
        </tr>
        @if(count($leadAppointmentData))
            <?php $grandcount = 0;?>
            @foreach($leadAppointmentData as $reportlead)
                <tr>
                    <td></td>
                    <td><?php echo $reportlead['name']; ?></td>
                    <td>{{ (array_key_exists($reportlead['region_id'], $filters['regions'])) ? $filters['regions'][$reportlead['region_id']]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportlead['city_id'], $filters['cities'])) ? $filters['cities'][$reportlead['city_id']]->name : '' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <?php $count = 0;?>
                @foreach($reportlead['children'] as $reportAppointments )
                    <tr>
                        <td> {{ $reportAppointments->patient->id }}</td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                                <a target="_blank"
                                   href="{{ route('admin.patients.preview',[$reportAppointments->patient->id]) }}">{{ $reportAppointments->patient->name }}</a>
                            @else
                                {{ $reportAppointments->patient->name }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reportAppointments->created_at)->format('M j, Y H:i A') }}</td>
                        <td>{{ (array_key_exists($reportAppointments->doctor_id, $filters['doctors'])) ? $filters['doctors'][$reportAppointments->doctor_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->service_id, $filters['services'])) ? $filters['services'][$reportAppointments->service_id]->name : '' }}</td>
                        <td>{{ $reportAppointments->patient->email }}</td>
                        <td>{{ ($reportAppointments->scheduled_date) ? \Carbon\Carbon::parse($reportAppointments->scheduled_date, null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportAppointments->scheduled_time, null)->format('h:i A') : '-' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->city_id, $filters['cities'])) ? $filters['cities'][$reportAppointments->city_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->location_id, $filters['locations'])) ? $filters['locations'][$reportAppointments->location_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->base_appointment_status_id, $filters['appointment_statuses'])) ? $filters['appointment_statuses'][$reportAppointments->base_appointment_status_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->appointment_type_id, $filters['appointment_types'])) ? $filters['appointment_types'][$reportAppointments->appointment_type_id]->name : '' }}</td>
                        <td>{{ (array_key_exists($reportAppointments->created_by, $filters['users'])) ? $filters['users'][$reportAppointments->created_by]->name : '' }}</td>
                    </tr>
                    <?php $count++; $grandcount++; ?>
                @endforeach
                <tr style="background-color:#3aaddc;color: #fff;">
                    <td></td>
                    <td><?php echo $reportlead['name']; ?></td>
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
                </tr>
            @endforeach
            <tr style=" background: #364150; color: #fff; font-weight: bold">
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
</div>

</body>
</html>

<script>
    function DoubleScroll(element) {
        var scrollbar = document.createElement('div');
        scrollbar.className='fake-scroll';
        scrollbar.appendChild(document.createElement('div'));
        scrollbar.style.overflow = 'auto';
        scrollbar.style.overflowY = 'hidden';
        scrollbar.firstChild.style.width = element.scrollWidth+'px';
        scrollbar.firstChild.style.paddingTop = '1px';
        scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
        scrollbar.onscroll = function() {
            element.scrollLeft = scrollbar.scrollLeft;
        };
        element.onscroll = function() {
            scrollbar.scrollLeft = element.scrollLeft;
        };
        element.parentNode.insertBefore(scrollbar, element);
    }

    DoubleScroll(document.getElementById('topscroll'));
</script>