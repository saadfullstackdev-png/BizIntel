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
    </style>
</head>
<body>
<div class="invoice-pdf">
    <table>
        <tr>
            <td><img class="logo" src="{{asset('centre_logo/')}}/{{ $location_info->image_src }}" class="img-responsive"
                     alt=""/></td>
            <td><h4 class="date">#{{ $pabao_payment->invoice_no }}
                    / <?php echo \Carbon\Carbon::parse($pabao_payment->created_at)->format('F j,Y'); ?></h4></td>
        </tr>
    </table>
    <table style="100%;">
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
            <td style="width:200px"><strong>Name:</strong><span
                        style="padding-left: 10px;">{{ $pabao_payment->first_name .' '. $pabao_payment->last_name }}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Name:</strong><span style="padding-left: 10px;">{{ $location_info->name }}</span><
            </td>
        </tr>
        <tr>
            <td><strong>Contact:</strong> <span style="padding-left: 10px;">{{ $pabao_payment->mobile }}</span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Contact:</strong> <span
                        style="padding-left: 10px;">{{ $company_phone_number->data }}</span></td>
        </tr>
        {{--<tr>
            <td><strong>Email:</strong> <span style="padding-left: 10px;"></span></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td><!-- left empty --></td>
            <td colspan="3"><strong>Email:</strong> <span style="padding-left: 10px;">{{$company_phone_number->email}}</span></td>

        </tr>--}}
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
            <td colspan="3" style="width:130px"><strong>Clinic Name:</strong> <span
                        style="padding-left: 10px;">{{ $location_info->name }}</span></td>
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
            <td colspan="3"><strong>Address:</strong> <span
                        style="padding-left: 10px;">{{ $location_info->address }}</span></td>
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
            <td colspan="3"><strong>NTN:</strong> <span style="padding-left: 10px;">{{ $location_info->ntn }}</span>
            </td>
        </tr>
    </table>
    <h3>Details</h3>
    <table class="table">
        <tr>
            <th> Total Amount</th>
            <th> Paid Amount</th>
            <th> Outstanding Amount</th>
        </tr>
        <tr>
            <td>{{ number_format($totol_amount,2) }}</td>
            <td>{{ number_format($paid_amount,2) }}</td>
            <td>{{ number_format($outstanding_amount,2) }}</td>
        </tr>
    </table>
    <h3>Payment</h3>
    <table class="table">
        <tr>
            <th>Date</th>
            <th>Amount</th>
        </tr>
        @if(count($payment_pabau_history))
            @foreach($payment_pabau_history as $pay)
                <tr>
                    <td>{{\Carbon\Carbon::parse($pay->date_paid)->format('F j,Y')}}</td>
                    <td>{{number_format($pay->amount,2)}}</td>
                </tr>
            @endforeach
        @endif
    </table>
</div>
{{--<table style="width: 100%;">
    <tr>
        <td><div class="danger-alert">Invoice is not Refundable</div></td>
    </tr>
</table>--}}
</body>

</html>