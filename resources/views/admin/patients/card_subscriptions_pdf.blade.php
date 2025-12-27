<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Card Subscriptions Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Card Subscriptions Report</h1>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    @if($fromDate || $toDate)
    <div class="filter-info">
        <strong>Filters Applied:</strong>
        @if($fromDate)
            From Date: {{ $fromDate }}
        @endif
        @if($toDate)
            To Date: {{ $toDate }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Card Number</th>
                <th>Patient Name</th>
                <th>Patient Phone</th>
                <th>Subscription Date</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->id }}</td>
                <td>{{ $subscription->card_number }}</td>
                <td>{{ $subscription->patient ? $subscription->patient->name : '-' }}</td>
                <td>{{ $subscription->patient ? $subscription->patient->phone : '-' }}</td>
                <td>{{ $subscription->subscription_date ? date('Y-m-d', strtotime($subscription->subscription_date)) : '-' }}</td>
                <td>{{ $subscription->expiry_date ? date('Y-m-d', strtotime($subscription->expiry_date)) : '-' }}</td>
                <td>
                    @if($subscription->is_active == 1)
                        <span class="status-active">Active</span>
                    @else
                        <span class="status-inactive">Inactive</span>
                    @endif
                </td>
                <td class="amount">{{ number_format($subscription->amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7"><strong>Total Amount:</strong></td>
                <td class="amount"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Total Records: {{ count($subscriptions) }}</p>
    </div>
</body>
</html>