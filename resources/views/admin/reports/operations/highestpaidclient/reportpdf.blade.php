@inject('request', 'Illuminate\Http\Request')
        <!DOCTYPE html>
<html>
<head>
    <style>
        .invoice-pdf{
            width: 100%;
        }
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
        .table{
            width: 100%;
        }
        .table th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        td, th {
            text-align: left;
            font-size: 12px;
        }

        .table td, .table th {
            text-align: left;
            padding: 5px;
            font-size: 12px;
        }
        table.table tr td{
            padding: 12px 5px;
        }
        table.table tr:first-child{
            background-color: #fff;
        }
        .table tr:nth-child(odd) {
            background-color: #dddddd;
        }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding: 8px;
            line-height: 1.42857;
            vertical-align: top;
            border-top: 1px solid #e7ecf1;
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
                        <td>Higest Paid Client Report</td>
                    </tr>
                    <tr>
                        <td style="width: 70px;">Duration</td>
                        <td>For the month of {{ \Carbon\Carbon::createFromDate($request->get("year"), $request->get("month"), 1)->format('M Y')}}</td>
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
        <tr style=" color: #fff; background-color: #364150;>
            <th width="15%">
            <th>ID</th>
            <th>Client Name</th>
            <th>Email</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Revenue</th>
        </tr>
        @if(count($reportData))
            @foreach($reportData as $reportlocationdata)
                <tr>
                    <td><?php echo $reportlocationdata['name']; ?></td>
                    <td><?php echo $reportlocationdata['region']; ?></td>
                    <td><?php echo $reportlocationdata['city']; ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($reportlocationdata['clients'] as $reportRow )
                    <tr>
                        <td>{{$reportRow['id']}}</td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                                <a target="_blank"
                                   href="{{ route('admin.patients.preview',[$reportRow['id']]) }}">{{ $reportRow['name']}}</a>
                            @else
                                {{ $reportRow['name']}}
                            @endif
                        </td>
                        <td>{{$reportRow['email']}}</td>
                        <td>{{$reportRow['gender']}}</td>
                        <td>{{$reportRow['dob']}}</td>
                        <td style="text-align: right"><?php echo number_format($reportRow['Revenue'], 2); ?></td>
                    </tr>
                @endforeach
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