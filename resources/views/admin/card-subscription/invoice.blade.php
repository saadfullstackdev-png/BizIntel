<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            margin: 10px 20px;
        }

            table {
                font-size: 12px;
            }

            .tr-root-group {
                background-color: #F3F3F3;
                color: rgba(0, 0, 0, 0.98);
                font-weight: bold;
            }

            .tr-group {
                font-weight: bold;
            }

            .bold-text {
                font-weight: bold;
            }

            .error-text {
                font-weight: bold;
                color: #FF0000;
            }

            .ok-text {
                color: #006400;
            }
    </style>
</head>
<body>
    <div class="sn-table-holder" style="background-color: #2b3643;">
        <div class="sn-report-head">
            <div class="sn-title">
                <h1 style="color:#fff">Card Subscription</h1>
            </div>
        </div>
    </div>
    <div class="panel-body sn-table-body">
        <div class="bordered">
            <div class="sn-table-head">
                <div class="row">
                    <div class="col-md-2">
                        <img src="{{ asset('centre_logo/logo_final.png') }}" height="80">
                    </div>
                    <div class="col-md-6">&nbsp;</div>
                    <div class="col-md-4">
                        <table class="dark-th-table table table-bordered">
                            <tr>
                                <th width="25%" style="background-color: #2b3643; color:#fff"><strong>Subscription Date:</strong> </th>
                                <td>{{ $subscription->subscription_date }}</td>
                            </tr>
                            <tr>
                                <th width="25%" style="background-color: #2b3643; color:#fff"><strong>Expiry Date:</strong> </th>
                                <td>{{ $subscription->expiry_date }}</td>
                            </tr>
                            <tr>
                                <th style="background-color: #2b3643; color:#fff">Date</th>
                                <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
    
                <div class="table-wrapper" id="topscroll">
                    <table class="table">
                        <thead style="background-color: #2b3643; color:#fff">
                            <th>ID</th>
                            <th>Card Number</th>
                            <th>Patient Name</th>
                            <th>Subscription Date</th>
                            <th>Expiry Date</th>
                            <th>Amount</th>
                        </thead>
                        <tbody>
                            <tr>
                                <th>1</th>
                                <td>{{ $subscription->card_number }}</td>
                                <td>{{ $subscription->patient->name ?? 'N/A' }}</td>
                                <td>{{ $subscription->subscription_date }}</td>  
                                <td>{{ $subscription->expiry_date }}</td>  
                                <td>{{ $charges }}</td>  
                            </tr>        
                        </tbody>
                        <tfoot style="background-color: #2b3643; color:#fff">
                            <tr>
                                <td colspan=4><strong>Total</strong></td>
                                 
                                <td></td>  
                                <td>{{ $charges }}</td>
                            </tr> 
                        </tfoot>
                    </table>
    
                    
                </div>
            </div>
        </div>
        <div class="clear clearfix"></div>
    </div>
</body>
</html>
