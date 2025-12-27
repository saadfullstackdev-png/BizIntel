@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
@if($request->get('medium_type') == 'pdf')
@include('partials.pdf_head')
@else
@include('partials.head')
@endif
<style type="text/css">
    @page {
        margin: 10px 20px;
    }

    @media print {
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
    }
</style>
@endif
<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>Summary Report</h1>
        </div>  
        <div class="sn-buttons">
            @if($request->get('medium_type') == 'web')
            <a class="btn sn-white-btn btn-default" href="javascript:;"
                onclick="FormControls.printReport('excel');">
                <i class="fa fa-file-excel-o"></i><span>Excel</span>
            </a>
            <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="FormControls.printReport('pdf');">
                <i class="fa fa-file-pdf-o"></i><span>PDF</span>
            </a>
            <a class="btn sn-white-btn btn-default" href="javascript:;"
                onclick="FormControls.printReport('print');">
                <i class="fa fa-print"></i><span>Print</span>
            </a>
            @endif
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
                            <th width="25%">Duration</th>
                            <td>From {{ $start_date }} to {{ $end_date }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                        </tr>
                        @if($request->get('patient_id'))
                        <tr>
                            <th>Patient</th>
                            <td>{{ $patientName ?? 'N/A' }}</td>
                        </tr>
                        @endif
                        @if($request->get('doctor_id'))
                        <tr>
                            <th>Doctor
                            <td>{{ $doctorName ?? 'N/A' }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div><!-- End sn-table-head -->
        <div class="column-toggle">  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="0"> City</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="1"> Center</label>  
            @foreach($leadSources as $index => $source)  
                <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ $index + 2 }}"> {{ $source->name }}</label>  
            @endforeach  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="2"> Total Leads</label> 
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 3 }}"> Booked</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 4 }}"> Conversion Ratio (Booked/Total Leads)</label>
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 5 }}"> Arrived</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 6 }}"> Not Arrived</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 7 }}"> Conversion Ratio (Arrived/Booked)</label>
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 8 }}"> Converted</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 9 }}"> Not Converted</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 10 }}"> Conversion Ratio (Converted/Arrived)</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 11 }}"> Conversion Ratio (Converted/Total Leads)</label>
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 12 }}"> Conversion to Revenue</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 13 }}"> Total Revenue Cash In</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 14 }}"> Total Revenue Card In</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 15 }}"> Total Revenue Bank In</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 16 }}"> Total Revenue Wallet In</label>  
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 17 }}"> Total Refund</label>
            <label><input type="checkbox" checked="checked" class="column-checkbox" data-column="{{ count($leadSources) + 18 }}"> Total Hand-In</label>  
        </div>  
        
        <button id="showAll">Show All</button> 
        <button id="hideAll">Hide All</button> 
        <div class="table-wrapper" id="topscroll">
            <table class="table toogle">
                <thead>
                    <tr>
                        <th>City</th>
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
                        @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                            <th>Converted</th>
                        @endif
                        @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'not-converted')
                            <th>Not Converted</th>
                        @endif
                        <th>Conversion Ratio (Converted/Arrived)</th>
                        <th>Conversion Ratio (Converted/Total Leads)</th>
                        <th>Conversion to Revenue</th>
                        <th>Total Revenue Cash In</th>
                        <th>Total Revenue Card In</th>
                        <th>Total Revenue Bank In</th>
                        <th>Total Revenue Wallet In</th>
                        <th>Total Refund</th>
                        <th>Total Hand-In</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($summaryData) > 0)
                    @php
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
                        @foreach($summaryData as $data)
                        @php
                            $totalLeadsSum += $data['total_leads'];
                            $bookedSum += $data['booked'];
                            foreach ($leadSources as $source) {
                                $sourcesSum[$source->name] += $data[$source->name];
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
                                <td>{{ $data['city'] }}</td>
                                <td>{{ $data['center'] }}</td>
                                @foreach($leadSources as $source)
                                    <td>{{ $data[$source->name] }}</td>
                                @endforeach
                                <td>{{ $data['total_leads'] }}</td>
                                {{-- <td>{{ $data['booked'] }}</td> --}}
                                <td>
                                    <form action="{{ url('admin/appointmentreports/appointments-general-load') }}" method="POST">
                                        @csrf
                                        {{-- <input type="hidden" name="request" value="{{ $request->all() }}"> --}}
                                        @foreach($request->all() as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                                        @endforeach
                                        <input type="hidden" name="report_type" value="general">
                                        <input type="hidden" name="request" value="request">
                                        <input type="hidden" name="location_id" value="{{ $data['center_id'] }}">
                                        {{-- <input type="hidden" name="appointment_type_id" value="1"> --}}
                                        <input type="hidden" name="is_converted" value="all">
                                        {{-- <input type="hidden" name="appointment_status_id" value="2"> --}}
                                        <button class="btn btn-link" type="submit">
                                            {{ $data['booked'] }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ number_format($data['conversion_ratio_1'], 3) }} %</td>
                                {{-- <td>{{ $data['arrived'] }}</td> --}}
                                <td>
                                    <form action="{{ url('admin/appointmentreports/appointments-general-load') }}" method="POST">
                                        @csrf
                                        {{-- <input type="hidden" name="request" value="{{ $request->all() }}"> --}}
                                        @foreach($request->all() as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                                        @endforeach
                                        <input type="hidden" name="report_type" value="general">
                                        <input type="hidden" name="request" value="request">
                                        <input type="hidden" name="location_id" value="{{ $data['center_id'] }}">
                                        <input type="hidden" name="appointment_type_id" value="1">
                                        <input type="hidden" name="is_converted" value="all">
                                        <input type="hidden" name="appointment_status_id" value="2">
                                        <button class="btn btn-link" type="submit">
                                            {{ $data['arrived'] }}
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $data['not_arrived'] }}</td>
                                <td>{{ number_format($data['conversion_ratio_2'], 3) }} %</td>
                                {{-- <td>
                                    <form action="{{ url('admin/appointmentreports/appointments-general-load') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="request" value="{{ $request->all() }}">
                                        @foreach($request->all() as $key => $value)
                                                <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                                        @endforeach
                                        <input type="hidden" name="report_type" value="general">
                                        <input type="hidden" name="request" value="request">
                                        <input type="hidden" name="location_id" value="{{ $data['center_id'] }}">
                                        <input type="hidden" name="appointment_type_id" value="1">
                                        <input type="hidden" name="is_converted" value="all">
                                        <input type="hidden" name="appointment_status_id" value="">
                                        <button class="btn btn-link" type="submit">
                                            {{ $data['not_arrived'] }}
                                        </button>
                                    </form>
                                </td> --}}
                                @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                                    {{-- <td>{{ $data['converted'] }}</td> --}}

                                    <td>
                                        <form action="{{ url('admin/appointmentreports/appointments-general-load') }}" method="POST">
                                            @csrf
                                            {{-- <input type="hidden" name="request" value="{{ $request->all() }}"> --}}
                                            @foreach($request->all() as $key => $value)
                                                    <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                                            @endforeach
                                            <input type="hidden" name="report_type" value="general">
                                            <input type="hidden" name="request" value="request">
                                            <input type="hidden" name="location_id" value="{{ $data['center_id'] }}">
                                            <input type="hidden" name="appointment_type_id" value="1">
                                            <input type="hidden" name="is_converted" value="converted">
                                            <input type="hidden" name="appointment_status_id" value="2">
                                            <button class="btn btn-link" type="submit">
                                                {{ $data['converted'] }}
                                            </button>
                                        </form>
                                    </td>
                                                                     
                                @endif
                                @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'not-converted')
                                    {{-- <td> {{ $data['not_converted'] }} </td> --}}
                                    <td>
                                        <form action="{{ url('admin/appointmentreports/appointments-general-load') }}" method="POST">
                                            @csrf
                                            {{-- <input type="hidden" name="request" value="{{ $request->all() }}"> --}}
                                            @foreach($request->all() as $key => $value)
                                                    <input type="hidden" name="{{ $key }}" value="{{ is_array($value) ? json_encode($value) : $value }}">
                                            @endforeach
                                            <input type="hidden" name="report_type" value="general">
                                            <input type="hidden" name="request" value="request">
                                            <input type="hidden" name="location_id" value="{{ $data['center_id'] }}">
                                            <input type="hidden" name="appointment_type_id" value="1">
                                            <input type="hidden" name="is_converted" value="not-converted">
                                            <input type="hidden" name="appointment_status_id" value="2">
                                            <button class="btn btn-link" type="submit">
                                                {{ $data['not_converted'] }}
                                            </button>
                                        </form>
                                    </td>
                                @endif
                                <td>{{ number_format($data['conversion_ratio_3'], 3) }} %</td>
                                <td>{{ number_format($data['conversion_ratio'], 3) }} %</td>
                                <td>{{ number_format($data['conversion_to_revenue'], 3) }}</td>
                                <td>{{ number_format($data['revenue_cash_in'], 2) }}</td>
                                <td>{{ number_format($data['revenue_card_in'], 2) }}</td>
                                <td>{{ number_format($data['revenue_bank_in'], 2) }}</td>
                                <td>{{ number_format($data['revenue_wallet_in'], 2) }}</td>
                                <td>{{ number_format($data['refund'], 2) }}</td>
                                <td>{{ number_format($data['revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ 13 + count($leadSources) }}" align="center">No records found.</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        @php
                        $conversionRatio = $totalLeadsSum > 0 ? ($convertedSum / $totalLeadsSum) * 100 : 0;
                        $conversionRatio_1 = $totalLeadsSum > 0 ? ($bookedSum / $totalLeadsSum) * 100 : 0;
                        $conversionRatio_2 = $bookedSum > 0 ? ($arrivedSum / $bookedSum) * 100 : 0;
                        $conversionRatio_3 = $arrivedSum > 0 ? ($convertedSum / $arrivedSum) * 100 : 0;
                        $conversionToRevenue = $convertedSum > 0 ? ($convertedRevenueSum / $convertedSum) : 0;
                        @endphp
                        <td><strong>Totals</strong></td>
                        <td></td>
                        @foreach($leadSources as $source)
                            <td>{{ $sourcesSum[$source->name] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalLeadsSum ?? 0 }}</td>
                        <td>{{ $bookedSum ?? 0 }}</td>
                        <td>{{ number_format($conversionRatio_1 ?? 0, 2) }}</td>
                        <td>{{ $arrivedSum ?? 0 }}</td>
                        <td>{{ $notArrivedSum ?? 0 }}</td>
                        <td>{{ number_format($conversionRatio_2 ?? 0, 2) }}</td>
                        @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'converted')
                            <td>{{ $convertedSum ?? 0 }}</td>
                        @endif
                        @if($request->get('is_converted') === 'all' || $request->get('is_converted') === 'not-converted')
                            <td>{{ $notConvertedSum ?? 0 }}</td>
                        @endif
                        <td>{{ number_format($conversionRatio_3 ?? 0, 2) }}</td>
                        <td>{{ number_format($conversionRatio ?? 0, 2) }}</td>
                        <td>{{ number_format($conversionToRevenue ?? 0, 2) }}</td>
                        <td>{{ number_format($revenueCashSum ?? 0, 2) }}</td>
                        <td>{{ number_format($revenueCardSum ?? 0, 2) }}</td>
                        <td>{{ number_format($revenueBankSum ?? 0, 2) }}</td>
                        <td>{{ number_format($revenueWalletSum ?? 0, 2) }}</td>
                        <td>{{ number_format($revenuerefundSum ?? 0, 2) }}</td>
                        <td>{{ number_format($revenuetotalSum ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
                
            </table>
        </div>
             
    </div>
    <div class="clear clearfix"></div>
    <!-- Liabilities and Assets -->
    <script>  
        // Function to toggle column visibility based on checkbox state  
        function toggleColumn(columnIndex, isVisible) {  
            const table = document.querySelector('.toogle');  
            if (table) {  
                for (const row of table.rows) {  
                    row.cells[columnIndex].style.display = isVisible ? '' : 'none';  
                }  
            }  
        }  
        
        // Function to handle checkbox changes  
        function handleCheckboxChange(event) {  
            const columnIndex = parseInt(event.target.getAttribute('data-column'));  
            const isChecked = event.target.checked;  
            toggleColumn(columnIndex, isChecked);  
        }  
        
        // Function to show all columns  
        function showAllColumns() {  
            const checkboxes = document.querySelectorAll('.column-checkbox');  
            checkboxes.forEach((checkbox) => {  
                checkbox.checked = true; // Check all checkboxes  
                toggleColumn(parseInt(checkbox.getAttribute('data-column')), true); // Show all columns  
            });  
        } 

        function hideAllColumns() {  
            const checkboxes = document.querySelectorAll('.column-checkbox');  
            checkboxes.forEach((checkbox) => {  
                checkbox.checked = false; // Check all checkboxes  
                toggleColumn(parseInt(checkbox.getAttribute('data-column')), false); // Show all columns  
            });  
        } 
        
        document.querySelectorAll('.column-checkbox').forEach((checkbox) => {  
            checkbox.addEventListener('change', handleCheckboxChange);  
        });  
        
        // Event listener for the Show All button  
        document.getElementById('showAll').addEventListener('click', showAllColumns);
        document.getElementById('hideAll').addEventListener('click', hideAllColumns);  
        </script>
    <script src="{{ url('js/admin/scrollbar/scrollbardev.js') }}" type="text/javascript"></>
</div>