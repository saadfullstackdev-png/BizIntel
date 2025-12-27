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
            <h1>{{ 'Incentive Detail Report'  }}</h1>
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
<?php $count = 1; ?>
    <table class="table">
        <tr style="background: #364150;color: #fff;font-weight: bold;">
            <th>@lang('global.leads.fields.full_name')</th>
            <th>@lang('global.leads.fields.email')</th>
            <th>@lang('global.leads.fields.phone')</th>
            <th>@lang('global.leads.fields.gender')</th>
            <th>@lang('global.leads.fields.role')</th>
            <th>@lang('global.leads.fields.region')</th>
            <th>@lang('global.leads.fields.city')</th>
            <th>@lang('global.leads.fields.location')</th>
            <th>@lang('global.leads.fields.total_Revenue')</th>
            <th>@lang('global.leads.fields.commission')</th>
            <th>@lang('global.leads.fields.incentive')</th>
        </tr>
        @if(count($reportData))
            <?php $total = 0; $rtotal = 0;?>
            @foreach($reportData as $user)
                <tr>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['phone'] }}</td>
                    <td>{{ $user['gender'] }}</td>
                    <td>{{ $user['Role'] }}</td>
                    <td>{{ $user['Region'] }}</td>
                    <td>{{ $user['City'] }}</td>
                    <td>{{ $user['Location'] }}</td>
                    <td style="text-align: right">
                        <?php
                        $rtotal += $user['TotalRevenue'];
                        echo number_format($user['TotalRevenue'], 2)
                        ?>
                    </td>
                    <td>{{$user['commission']}}</td>
                    <td style="text-align: right">
                        <?php
                        $total += $user['Incentive'];
                        echo number_format($user['Incentive'], 2);
                        ?>
                    </td>
                </tr>
                <tr style="font-weight: bold">
                    <td>Invoice No.</td>
                    <td>Service</td>
                    <td>Payment Date</td>
                    <td>Created by</td>
                    <td>Patient</td>
                    <td>Service Price</td>
                    <td>Discount Name</td>
                    <td>Discount Type</td>
                    <td>Discount Amount</td>
                    <td>Invoice Price</td>
                    <td>Centre</td>
                </tr>
                <?php  $grandserviceprice = 0;$grandtotalservice = 0;?>
                @foreach($user['detail'] as $reportRow)
                    <tr>
                        <td style="text-align: center;">{{ $reportRow['id']}}</td>
                        <td>{{ (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->name : '-' }}</td>
                        <td>{{ ($reportRow['created_at']) ? \Carbon\Carbon::parse($reportRow['created_at'], null)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($reportRow['created_at'], null)->format('h:i A') : '-' }}</td>
                        <td>{{ (array_key_exists($reportRow['created_by'], $filters['users'])) ? $filters['users'][$reportRow['created_by']]->name : '-' }}</td>
                        <td>{{ (array_key_exists($reportRow['patient_id'], $filters['patients'])) ? $filters['patients'][$reportRow['patient_id']]->name : '-' }}</td>
                        <td style="text-align: right;">
                            <?php
                            $grandserviceprice += (array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '';
                            echo number_format((array_key_exists($reportRow['service_id'], $filters['services'])) ? $filters['services'][$reportRow['service_id']]->price : '', 2);
                            ?>
                        </td>
                        <td>{{ (array_key_exists($reportRow['discount_id'], $filters['discounts'])) ? $filters['discounts'][$reportRow['discount_id']]->name : '-' }}</td>
                        <td>{{$reportRow['discount_type']==null?'-':$reportRow['discount_type']}}</td>
                        <td style="text-align: right;">{{$reportRow['discount_price']==null?'-':$reportRow['discount_price']}}</td>
                        <td style="text-align: right;">
                            <?php
                            $grandtotalservice += $reportRow['total_price'];
                            echo number_format($reportRow['total_price'], 2);
                            ?>
                        </td>
                        <td>{{ (array_key_exists($reportRow['location_id'], $filters['locations'])) ? $filters['locations'][$reportRow['location_id']]->name : '-' }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #37abdc;color: #fff;font-weight: bold;">
                    <td style="text-align: center;">Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;"><?php echo number_format($grandserviceprice, 2);?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;"><?php echo number_format($grandtotalservice, 2);?></td>
                    <td></td>
                </tr>
            @endforeach
            <tr style=" background: #364150; color: #fff;font-weight: bold;">
                <td><b>Grand Total</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right"><b>
                        <?php
                        echo number_format($rtotal, 2);
                        ?>
                    </b>
                </td>
                <td></td>
                <td style="text-align: right"><b>
                        <?php
                        echo number_format($total, 2);
                        ?>
                    </b>
                </td>
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