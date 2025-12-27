@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <title>{{ ( $performance ) ? 'My Performance By Centre Report' : 'Revenue By Centre Report' }}</title>
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
            font-size: 12px;
            padding: 8px;
        }
        .table td, .table th {
            text-align: left;
            padding: 5px;
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
                        <td>{{ ($performance) ? 'My Revenue By Centre Report' : 'Revenue By Centre Report' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>From:&nbsp;<strong>{{ $start_date }}</strong>&nbsp;To:&nbsp;<strong>{{ $end_date }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Date</td>
                        <td><strong>{{ Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="table">
        <tr style="background: #364150;color: #fff;">
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
                        echo number_format((array_key_exists($reportRow->service_id, $filters['services'])) ? $filters['services'][$reportRow->service_id]->price : '', 2);
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
                        echo number_format($reportRow->total_price, 2);
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
                <td style="text-align: right;"><?php echo number_format($grandserviceprice, 2);?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;"><?php echo number_format($grandtotalservice, 2);?></td>

            </tr>
        @else
            <tr>
                <td colspan="12" align="center">No record round.</td>
            </tr>
        @endif
    </table>
</div>

</body>
</html>