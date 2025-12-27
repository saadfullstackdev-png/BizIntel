@inject('request', 'Illuminate\Http\Request')
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
                margin: 0mm; 
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

        .table {
            width: 100%;
        }

        .table th, .table td {
            border: 1px solid #dddddd;
            padding: 3px;
            font-size: 10px;
            text-align: left;
        }

        .table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table tr:first-child {
            background-color: #364150;
            color: #ffffff;
        }

        .header-info {
            color: #364150;
        }

        .shdoc-header {
            color: #fff;
            background-color: #364150;
        }
    </style>
</head>
<body>
<div class="report-summary">
    <table>
        <tr>
            <td><img class="logo" src="{{ asset('centre_logo/logo_final.png') }}" alt="Logo"></td>
            <td style="text-align: right;">
                <table>
                    <tr>
                        <td style="width: 80px;">Report:</td>
                        <td>Lead Summary Report</td>
                    </tr>
                    <tr>
                        <td style="width: 80px;">Duration:</td>
                        <td>From <strong>{{ $start_date }}</strong> to <strong>{{ $end_date }}</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 80px;">Date:</td>
                        <td><strong>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="table">
        <tr class="shdoc-header">
            <th>Center</th>
            @foreach($leadSources as $source)
                <th>{{ $source->name }}</th>
            @endforeach
            <th>Total Leads</th>
            <th>Booked</th>
            <th>Conversion Ratio (Booked/Total Leads)</th>
            <th>Arrived</th>
            <th>Not Arrived</th>
            <th>Conversion Ratio (Arrived/Booked)</th>
            {{-- <th>Consultancy</th> --}}
            <th>Converted</th>
            <th>Not Converted</th>
            <th>Conversion Ratio (Converted/Arrived)</th>
            {{-- <th>Converted Revenue</th> --}}
            <th>Conversion Ratio (Converted/Total Leads)</th>
            {{-- <th>Conversion to Revenue</th>
            <th>Revenue Paid</th>
            <th>Total Revenue Cash In</th>
            <th>Total Revenue Card In</th>
            <th>Total Revenue Bank In</th>
            <th>Total Revenue Wallet In</th>
            <th>Total Refund</th>
            <th>Total Hand-In</th> --}}
        </tr>

        @php
            // Initialize totals for each column
            $totalLeadsSum = 0;
            $bookedSum = 0;
            $sourcesSum = [];
            $arrivedSum = 0;
            $notArrivedSum = 0;
            $consultancySum= 0;
            $convertedSum = 0;
            $notConvertedSum = 0;
            $convertedRevenueSum = 0;
            $revenuePaidSum = 0;
            $revenueCashSum = 0;
            $revenueCardSum = 0;
            $revenueBankSum = 0;
            $revenueWalletSum = 0;
            $revenuerefundSum = 0;
            $revenuetotalSum = 0;

            foreach ($leadSources as $source) {
                $sourcesSum[$source->name] = 0;
            }
        @endphp

        @forelse ($summaryData as $data)
            @php
                // Accumulate totals for each column
                $totalLeadsSum += $data['total_leads'];
                $bookedSum += $data['booked'];
                foreach ($leadSources as $source) {
                    $sourcesSum[$source->name] += $data[$source->name] ?? 0;
                }
                $arrivedSum += $data['arrived'];
                $notArrivedSum += $data['not_arrived'];
                $consultancySum += $data['consultancy'];
                $convertedSum += $data['converted'];
                $notConvertedSum += $data['not_converted'];
                $convertedRevenueSum += $data['converted_revenue'];
                $revenuePaidSum += $data['revenuepaid'];
                $revenueCashSum += $data['revenue_cash_in'];
                $revenueCardSum += $data['revenue_card_in'];
                $revenueBankSum += $data['revenue_bank_in'];
                $revenueWalletSum += $data['revenue_wallet_in'];
                $revenuerefundSum += $data['refund'];
                $revenuetotalSum += $data['revenue'];
            @endphp
            <tr>
                <td>{{ $data['center'] }}</td>
                
                @foreach($leadSources as $source)
                    <td>{{ $data[$source->name] }}</td>
                @endforeach
                <td>{{ $data['total_leads'] }}</td>
                <td>{{ $data['booked'] }}</td>
                <td>{{ number_format($data['conversion_ratio_1'],3) }} %</td>
                <td>{{ $data['arrived'] }}</td>
                <td>{{ $data['not_arrived'] }}</td>
                <td>{{ number_format($data['conversion_ratio_2'],3) }} %</td>
                {{-- <td>{{ $data['consultancy'] }}</td> --}}
                <td>{{ $data['converted'] }}</td>
                <td>{{ $data['not_converted'] }}</td>
                <td>{{ number_format($data['conversion_ratio_3'],3) }} %</td>
                {{-- <td>{{ $data['converted_revenue'] }}</td> --}}
                <td>{{ number_format($data['conversion_ratio'],3) }} %</td>
                {{-- <td>{{ number_format($data['conversion_to_revenue'],3) }}</td>
                <td>{{ number_format($data['revenuepaid'], 2) }}</td>
                <td>{{ number_format($data['revenue_cash_in'], 2) }}</td>
                <td>{{ number_format($data['revenue_card_in'], 2) }}</td>
                <td>{{ number_format($data['revenue_bank_in'], 2) }}</td>
                <td>{{ number_format($data['revenue_wallet_in'], 2) }}</td>
                <td>{{ number_format($data['refund'], 2) }}</td>
                <td>{{ number_format($data['revenue'], 2) }}</td> --}}
            </tr>
        @empty
            <tr>
                <td colspan="{{ 13 + $leadSources->count() }}" align="center">No record found.</td>
            </tr>
        @endforelse

        <!-- Footer with totals -->
        <tfoot>
            <tr>
                <td><strong>Totals</strong></td>
                @foreach($leadSources as $source)
                    <td>{{ $sourcesSum[$source->name] }}</td>
                @endforeach
                <td>{{ $totalLeadsSum }}</td>
                <td>{{ $bookedSum }}</td>
                <td>-</td> 
                <td>{{ $arrivedSum }}</td>
                <td>{{ $notArrivedSum }}</td>
                <td>-</td> 
                {{-- <td>{{ $consultancySum }}</td> --}}
                <td>{{ $convertedSum }}</td>
                <td>{{ $notConvertedSum }}</td>
                <td>-</td> 
                {{-- <td>{{ $convertedRevenueSum }}</td> --}}
                <td>-</td> 
                {{-- <td>-</td>  --}}
                {{-- <td>{{ number_format($revenuePaidSum, 2) }}</td> --}}
                {{-- <td>{{ number_format($revenueCashSum, 2) }}</td>
                <td>{{ number_format($revenueCardSum, 2) }}</td>
                <td>{{ number_format($revenueBankSum, 2) }}</td>
                <td>{{ number_format($revenueWalletSum, 2) }}</td>
                <td>{{ number_format($revenuerefundSum, 2) }}</td>
                <td>{{ number_format($revenuetotalSum, 2) }}</td> --}}
            </tr>
        </tfoot>
    </table>
</div>    
</body>
</html>