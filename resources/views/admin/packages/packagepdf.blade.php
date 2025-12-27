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

        .danger-alert {
            color: #000;
            border: 1px solid #f5c6cb;
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
    <table>
        <tr>
            <td><img class="logo" src="{{asset('centre_logo/')}}/{{$location_info->image_src}}" class="img-responsive"
                     alt=""/></td>
            <td><h4 class="date">#{{$package->name}}
                    / <?php echo \Carbon\Carbon::parse($package->created_at)->format('F j,Y'); ?></h4></td>
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
            <td style="width:200px"><strong>Name:</strong> <span
                        style="padding-left: 10px;">{{$package->user->name}}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Name:</strong><span style="padding-left: 10px;">{{$account_info->name}}</span><</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> <span style="padding-left: 10px;">{{$package->user->email}}</span></td>
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
            <td><strong>Customer ID:</strong> <span style="padding-left: 10px;">{{$package->user->id}}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Email:</strong> <span style="padding-left: 10px;">{{$account_info->email}}</span>
            </td>
        </tr>
        <tr>
            <td></td>
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
            <td></td>
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
            <td></td>
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
            <td></td>
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
            <td></td>
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
            <th>Service Name</th>
            <th>Service Price</th>
            <th>Discount Name</th>
            <th>Discount Type</th>
            <th>Discount Price</th>
            <th>Subtotal</th>
            <th>Tax %</th>
            <th>Tax Price</th>
            <th>Total</th>
        </tr>
        @if($packagebundles)
            @foreach($packagebundles as $packagebundles)
                <tr>
                    <td><?php echo $packagebundles->bundle->name; ?></td>
                    <td>{{number_format($packagebundles->service_price)}}</td>
                    <td>
                        @if($packagebundles->discount_id == null)
                            {{'-'}}
                        @elseif($packagebundles->discount_name)
                            {{$packagebundles->discount_name}}
                        @else
                            {{$packagebundles->discount->name}}
                        @endif
                    </td>
                    <td><?php if ($packagebundles->discount_type == null) {
                            echo '-';
                        } else {
                            echo $packagebundles->discount_type;
                        } ?>
                    </td>
                    <td><?php if ($packagebundles->discount_price == null) {
                            echo '0.00';
                        } else {
                            echo $packagebundles->discount_price;
                        } ?>
                    </td>
                    <td>{{$packagebundles->tax_exclusive_net_amount}}</td>
                    <td>{{$packagebundles->tax_percenatage}}</td>
                    <td>{{$packagebundles->tax_price}}</td>
                    <td>{{$packagebundles->tax_including_price}}</td>
                </tr>
            @endforeach
        @endif
    </table>
    <table class="grand-tax">
        <tbody>
        <tr>
            <td style="text-align: right;"><strong>Total:</strong> <?php echo number_format($package->total_price);?>/-</td>
        </tr>
        <tr>
            <td><strong>Note:</strong> All treatment prices are inclusive of taxes</td>
        </tr>
        </tbody>
    </table>
    <br>
    <div>
        <h3 style="margin-top: 0;">Cash Received </h3>
    </div>
    <table class="table">
        <tr>
            <th>Payment Mode</th>
            <th>Cash Flow</th>
            <th>Cash Amount</th>
            <th>Created At</th>
        </tr>
        @if($packageadvances)
            <?php $total_received = 0; ?>
            @foreach($packageadvances as $packageadvances)
                @if($packageadvances->cash_amount != '0' && $packageadvances->cash_flow == 'in')
                    <tr>
                        <td><?php echo $packageadvances->paymentmode ? $packageadvances->paymentmode->name : 'Wallet' ?></td>
                        <td><?php echo $packageadvances->cash_flow; ?></td>
                        <td><?php echo number_format($packageadvances->cash_amount) ?>/-</td>
                        <td><?php echo \Carbon\Carbon::parse($packageadvances->created_at)->format('F j,Y h:i A'); ?></td>
                    </tr>
                    <?php $total_received += $packageadvances->cash_amount; ?>
                @endif
            @endforeach
            <tr>
                <td><b>Total</b></td>
                <td></td>
                <td><b>{{number_format($total_received)}}/-</b></td>
                <td></td>
            </tr>
        @endif
    </table>
    <table class="grand-tax" style="margin-top: 30px;">
        <tr>
            <td>Thank you for your business with 3D lifestyle</td>
        </tr>
    </table>
</div>
</body>
</html>