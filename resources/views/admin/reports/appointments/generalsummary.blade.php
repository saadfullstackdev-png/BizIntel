@inject('request', 'Illuminate\Http\Request')
@if($request->get('medium_type') != 'web')
    @if($request->get('medium_type') == 'pdf')
        @include('partials.pdf_head')
    @else
        @include('partials.head')
    @endif
    <link rel="stylesheet" href="{{ asset('css/print-styles.css') }}">
@endif
@extends('layouts.app')
@section('stylesheets')
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #printable-report, #printable-report * {
            visibility: visible;
        }

        #printable-report {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        @page {
            margin: 1in;
        }

        .btn-print {
            display: none;
        }
    }
</style>

@endsection


@section('content')
@if($request->get('medium_type') == 'web')
    {{-- <a href="{{ route('export.excel', array_merge(request()->all(), ['medium_type' => 'excel'])) }}" 
        class="btn sn-white-btn btn-default">
         <i class="fa fa-file-excel-o"></i><span>Excel</span>
     </a>
     <a href="{{ route('export.pdf', array_merge(request()->all(), ['medium_type' => 'pdf'])) }}" 
        class="btn sn-white-btn btn-default">
         <i class="fa fa-file-pdf-o"></i><span>PDF</span>
     </a> --}}
        
    <a href="javascript:void(0)" class="btn sn-white-btn btn-default btn-print">
        <i class="fa fa-print"></i><span>Print</span>
    </a>   
    <a href="javascript:void(0)" class="btn sn-white-btn btn-default btn-download-pdf">
        <i class="fa fa-file-pdf-o"></i><span>Download PDF</span>
    </a>         
    <a href="javascript:void(0)" class="btn sn-white-btn btn-default btn-download-excel">
        <i class="fa fa-file-excel-o"></i><span>Download Excel</span>
    </a>    
    @endif
<div class="sn-table-holder" id="printable-report">
    <!-- Report Header -->
    <header class="sn-report-head" id="printable-report">
        <div class="sn-title" style="background-color: #2b3643; color:#ddd">
            <h1>Patient Record</h1>
        </div>
    </header>

    <!-- Report Body -->
    <div class="panel-body sn-table-body">
        <div class="row align-items-center">
            <div class="col-md-2">
                <img src="{{ asset('centre_logo/logo_final.png') }}" alt="Logo" class="img-fluid">
            </div>
            <div class="col-md-6 text-center">
                <!-- Center content can go here -->
            </div>
            <div class="col-md-4">
                <table class="table table-bordered">
                    <tr>
                        <th>Duration</th>
                        <td>From {{ $start_date }} to {{ $end_date }}</td>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Serial #</th>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Scheduled</th>
                        <th>Doctor</th>
                        <th>City</th>
                        <th>Centre</th>
                        <th>Lead Source</th>
                        <th>Treatment/Consultancy</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Consultancy Type</th>
                        <th>Converted</th>
                        <th>Created At</th>
                        <th>Created By</th>
                        <th>Updated By</th>
                        <th>Rescheduled By</th>
                        <th>Referred By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $reportRow)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $reportRow->patient_id }}</td>
                        <td>
                            @if($request->get('medium_type') == 'web')
                            <a target="_blank"
                                href="{{ route('admin.patients.preview', $reportRow->patient->id) }}">{{ $reportRow->patient->name }}</a>
                            @else
                            {{ $reportRow->patient->name }}
                            @endif
                        </td>
                        <td>{{ $reportRow->phone }}</td>
                        <td>{{ $reportRow->patient->email }}</td>
                        <td>{{ $reportRow->scheduled_date ? \Carbon\Carbon::parse($reportRow->scheduled_date)->format('M j, Y h:i A') : '-' }}</td>
                        <td>{{ $filters['doctors'][$reportRow->doctor_id]->name ?? '' }}</td>
                        <td>{{ $filters['cities'][$reportRow->city_id]->name ?? '' }}</td>
                        <td>{{ $filters['locations'][$reportRow->location_id]->name ?? '' }}</td>
                        <td>{{ $reportRow->lead->lead_source->name ?? '' }}</td>
                        <td>{{ $filters['services'][$reportRow->service_id]->name ?? '' }}</td>
                        <td>{{ $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name ?? '' }}</td>
                        <td>{{ $filters['appointment_types'][$reportRow->appointment_type_id]->name ?? '' }}</td>
                        <td>{{ $reportRow->consultancy_type == 'in_person' ? 'In Person' : 'Virtual' }}</td>
                        <td>{{ $reportRow->is_converted ? 'Converted' : 'Not Converted' }}</td>
                        <td>{{ $reportRow->created_at->format('M j, Y h:i A') }}</td>
                        <td>{{ $filters['users'][$reportRow->created_by]->name ?? '' }}</td>
                        <td>{{ $filters['users'][$reportRow->updated_by]->name ?? '' }}</td>
                        <td>{{ $filters['users'][$reportRow->rescheduled_by]->name ?? '' }}</td>
                        <td>{{ $filters['users'][$reportRow->referred_by]->name ?? '' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="19" class="text-center">No records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function printReport() {
        var printContent = document.getElementById('printable-report');
        var printWindow = window.open('', '', '');

        printWindow.document.write('<html><head><title>Patient Report</title>');
        printWindow.document.write('<link rel="stylesheet" href="{{ asset('css/print-styles.css') }}">'); // Include your print styles
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent.innerHTML); // Insert report content
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }

    function downloadPDF() {
        var element = document.getElementById('printable-report');

        // Configure the PDF options
        var options = {
            margin: 0.5,
            filename: 'Patient_Report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 4 },
            jsPDF: { unit: 'in', format: 'A3', orientation: 'Landscape' }
        };

        html2pdf().set(options).from(element).save();
    }

    document.querySelector('.btn-print').addEventListener('click', function (e) {
        e.preventDefault();
        printReport();
    });

    document.querySelector('.btn-download-pdf').addEventListener('click', function (e) {
        e.preventDefault();
        downloadPDF();
    });

    function downloadExcel() {
        // Data to be added to the Excel file
        const data = [
            ["ID", "Client", "Phone", "Email", "Scheduled", "Doctor", "City", "Centre", "Lead Source", "Treatment/Consultancy", "Status", "Type", "Consultancy Type", "Converted", "Created At", "Created By", "Updated By", "Rescheduled By", "Referred By"],
            @forelse($reportData as $reportRow)
            [
                "{{ $reportRow->patient_id }}",
                "{{ $reportRow->patient->name }}",
                "{{ $reportRow->phone }}",
                "{{ $reportRow->patient->email }}",
                "{{ $reportRow->scheduled_date ? \Carbon\Carbon::parse($reportRow->scheduled_date)->format('M j, Y h:i A') : '-' }}",
                "{{ $filters['doctors'][$reportRow->doctor_id]->name ?? '' }}",
                "{{ $filters['cities'][$reportRow->city_id]->name ?? '' }}",
                "{{ $filters['locations'][$reportRow->location_id]->name ?? '' }}",
                "{{ $reportRow->lead->lead_source->name ?? '' }}",
                "{{ $filters['services'][$reportRow->service_id]->name ?? '' }}",
                "{{ $filters['appointment_statuses'][$reportRow->base_appointment_status_id]->name ?? '' }}",
                "{{ $filters['appointment_types'][$reportRow->appointment_type_id]->name ?? '' }}",
                "{{ $reportRow->consultancy_type == 'in_person' ? 'In Person' : 'Virtual' }}",
                "{{ $reportRow->is_converted ? 'Converted' : 'Not Converted' }}",
                "{{ $reportRow->created_at->format('M j, Y h:i A') }}",
                "{{ $filters['users'][$reportRow->created_by]->name ?? '' }}",
                "{{ $filters['users'][$reportRow->updated_by]->name ?? '' }}",
                "{{ $filters['users'][$reportRow->rescheduled_by]->name ?? '' }}",
                "{{ $filters['users'][$reportRow->referred_by]->name ?? '' }}"
            ],
            @empty
            []
            @endforelse
        ];

        // Create a new workbook and add the data
        const workbook = XLSX.utils.book_new();
        const worksheet = XLSX.utils.aoa_to_sheet(data);

        // Append the worksheet to the workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, "Patient Report");

        // Export the workbook to a file
        XLSX.writeFile(workbook, "Patient_Report.xlsx");
    }

    // Attach event listener to the button
    document.querySelector('.btn-download-excel').addEventListener('click', function (e) {
        e.preventDefault();
        downloadExcel();
    });

</script>

@endsection
