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
            <h1>{{ ($performance) ? 'My Revenue By Centre Report' : 'Revenue By Centre Report' }}</h1>
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
    <table class="table">
        <tr>
            <th>Invoice No.</th>
            <th>Centre</th>
            <th>Service</th>
            <th>Payment Date</th>
            <th>Created by</th>
            <th>Patient</th>
            <th>Service Price</th>
            <th>Discount Name</th>
            <th>Discount Type</th>
            <th>Discount Price</th>
            <th>Subtotal</th>
            <th>Tax Amount</th>
            <th>Invoice Price/Total</th>
        </tr>
        @if(count($reportData))
            <?php $grandserviceprice = 0; $grandtotalservice = 0; ?>
            @foreach($reportData as $reportRow)
                <tr>
                    <td style="text-align: center;">{{ $reportRow->id }}</td>
                    <td>{{ (array_key_exists($reportRow->location_id, $filters['locations'])) ? $filters['locations'][$reportRow->location_id]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->name : '' }}</td>
                    <td>{{ ($reportRow->created_at) ? \Carbon\Carbon::parse($reportRow->created_at, null)->format('M j, Y').' at '.\Carbon\Carbon::parse($reportRow->created_at, null)->format('h:i A') : '-' }}</td>
                    <td>{{ (array_key_exists($reportRow->created_by, $filters['users'])) ? $filters['users'][$reportRow->created_by]->name : '' }}</td>
                    <td>{{ (array_key_exists($reportRow->patient_id, $filters['patients'])) ? $filters['patients'][$reportRow->patient_id]->name : '' }}</td>
                    <td style="text-align: right;">
                        <?php
                        $grandserviceprice += (array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '';
                        echo number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '',2);
                        ?>
                    </td>
                    <td>{{$reportRow->discount_name?$reportRow->discount_name:''}}</td>
                    <td>{{$reportRow->discount_type?$reportRow->discount_type:''}}</td>
                    <td style="text-align: right;">{{$reportRow->discount_price?$reportRow->discount_price:''}}</td>
                    <td style="text-align: right;">{{number_format($reportRow->tax_exclusive_serviceprice,2)}}</td>
                    <td style="text-align: right;">{{number_format($reportRow->tax_price,2)}}</td>
                    <td style="text-align: right;">
                        <?php
                        $grandtotalservice += $reportRow->total_price;
                        echo number_format($reportRow->total_price,2);
                        ?>
                    </td>
                </tr>
            @endforeach
            <tr style="background: #364150;color: #fff;">
                <td style="text-align: center;">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><?php echo number_format($grandserviceprice,2);?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><?php echo number_format($grandtotalservice,2);?></td>
            </tr>
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
            @endif
    </table>
</div>
</div>

</body>
</html>