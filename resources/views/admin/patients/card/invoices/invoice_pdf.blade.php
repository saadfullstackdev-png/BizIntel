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

        .table tr:nth-child(even) {
            background-color: #dddddd;
        }
        .danger-alert{
            color: #000;
            border:1px solid #f5c6cb;
            padding: 8px 10px;
            text-align: center;
            margin: 10px 0 0;
        }
        .grand-tax {
            margin-top: 0;
        }
        .grand-tax tr:first-child td {
            padding-bottom: 0;
        }
        .grand-tax tr:last-child td {
            padding-top: 0;
        }
        .grand-tax td {
            padding-left: 0;
            padding-right: 0;
        }
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table >
        <tr style="padding-left: 50%">
            @if($invoicestatus->slug == 'cancelled')
                <img src="{{ url('metronic/assets/pages/media/invoice/cancld.png') }}"
                     style="width: 20%;text-align: center;padding-left:43%" class="img-responsive" alt=""/>
            @endif
        </tr>
    </table>
    <br>
    <table>

        <tr>
            <td><img class="logo" src="{{asset('centre_logo/')}}/{{$location_info->image_src}}"
                     class="img-responsive" alt=""/></td>
            <td><h4 class="date">#{{$Invoiceinfo->id}}
                    / <?php echo \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('F j,Y'); ?></h4></td>
        </tr>
    </table>
    <table>
        <tr style="padding-top: 30px;">
            <th>Client</th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th><!-- left empty --></th>
            <th colspan="3" style="width: 250px;">Company</th>
        </tr>
        <tr>
            <td style="width:200px"><strong>Name:</strong><span style="padding-left: 10px;">{{$patient->name}}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Name:</strong><span style="padding-left: 10px;">{{$account->name}}</span><</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> <span style="padding-left: 10px;">{{$patient->email}}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Contact:</strong> <span style="padding-left: 10px;">{{$company_phone_number->data}}</span></td>
        </tr>
        <tr>
            <td><strong>Customer ID:</strong> <span style="padding-left: 10px;">{{$patient->id}}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Email:</strong> <span style="padding-left: 10px;">{{$account->email}}</span></td>

        </tr>
        <tr>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3" style="width:130px"><strong>Clinic Name:</strong> <span style="padding-left: 10px;">{{$location_info->name}}</span></td>
        </tr>
        <tr>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3" style="width:130px"><strong>Clinic Contact:</strong> <span style="padding-left: 10px;">{{$location_info->fdo_phone}}</span></td>
        </tr>
        <tr>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Address:</strong> <span style="padding-left: 10px;">{{$location_info->address}}</span></td>
        </tr>
        <tr>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>NTN:</strong> <span style="padding-left: 10px;">{{$location_info->ntn}}</span></td>
        </tr>
        <tr>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>STN:</strong> <span style="padding-left: 10px;">{{$location_info->stn}}</span></td>
        </tr>
    </table>
    <table class="table">
        <tr>
            <th> #</th>
            <th>Consultancy\Service</th>
            <th> Service Price</th>
            <th> Discount Name</th>
            <th> Discount Type</th>
            <th> Discount Price</th>
            <th> Subtotal</th>
            <th> Tax %</th>
            <th> Tax Price</th>
            <th> Total</th>
        </tr>
        <tr>
            <td> 1</td>
            <td>{{$service->name}} </td>
            <td>
                @if($Invoiceinfo->is_exclusive == '0')
                    {{number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price))}}
                @elseif($Invoiceinfo->is_exclusive == '1')
                    {{number_format($Invoiceinfo->service_price)}}
                @endif

            </td>
            <td>
                @if($discount != null)
                    {{$discount->name}}
                @else
                    -
                @endif
            </td>
            <td>
                @if($Invoiceinfo->discount_type != null)
                    {{$Invoiceinfo->discount_type}}
                @else
                    -
                @endif
            </td>
            <td>
                @if($Invoiceinfo->discount_price != null)
                    {{number_format($Invoiceinfo->discount_price)}}
                @else
                    -
                @endif
            </td>
            <td>
                @if($Invoiceinfo->is_exclusive == '0')
                    @if($Invoiceinfo->discount_price == null)
                        {{number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price))}}
                    @else
                        {{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}
                    @endif
                @elseif($Invoiceinfo->is_exclusive == '1')
                    {{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}
                @endif
            </td>
            <td>
                {{$Invoiceinfo->tax_percenatage}}
            </td>
            <td>
                {{$Invoiceinfo->tax_price}}
            </td>
            <td>
                {{number_format($Invoiceinfo->tax_including_price)}}
            </td>
        </tr>
    </table>
    <table class="grand-tax">
        <tbody>
        <tr>
            <td style="text-align: right;"><strong>Total:</strong> <?php echo number_format($Invoiceinfo->total_price);?>/-</td>
        </tr>
        <tr>
            <td><strong>Note:</strong> All treatment prices are inclusive of taxes</td>
        </tr>

        </tbody>
    </table>
</div>
<table style="width: 100%;">
    <tr>
        <td><div class="danger-alert">Invoice is not Refundable</div></td>
    </tr>
</table>

</body>
</html>