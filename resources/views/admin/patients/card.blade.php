@extends('layouts.app')

@section('stylesheets')
<style>
    .select2-selection {
        border: 1px solid #ced4da !important;
        padding: 5px !important;
        border-radius: 4px !important;
    }

    .filter-container {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .export-buttons {
        margin-bottom: 20px;
    }

    .btn {
        transition: all 0.2s ease;
        padding: 8px 16px;
        border-radius: 4px;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .form-group {
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .filter-container .form-group {
            width: 100%;
            margin-bottom: 10px;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
    <h1>Card Subscriptions</h1>
    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <!-- Action Buttons -->
    @if(Gate::allows('add_new_card_subscriptions'))
        <div class="action-buttons">
            <button class="btn btn-primary" data-toggle="modal" data-target="#subscriptionModal">
                <i class="fa fa-plus"></i> Add New Subscription
            </button>
        </div>
    @endif
    <!-- Filter Section -->
    <div class="filter-container">
        <div class="row">
            <div class="col-md-3 col-sm-6 form-group">
                <label for="user_location">Location</label>
                <select name="user_location" id="user_location_filter" class="form-control select2" required>
                    <option value="" disabled selected>Select Location</option>
                    @foreach ($userLocation as $item)
                        <option value="{{$item->id}}">{{$item->location_name}} {{$item->city_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6 form-group">
                <label for="from_date">From Date:</label>
                <input type="date" id="from_date" class="form-control">
            </div>
            <div class="col-md-3 col-sm-6 form-group">
                <label for="to_date">To Date:</label>
                <input type="date" id="to_date" class="form-control">
            </div>
            <div class="col-md-3 col-sm-6 form-group d-flex align-items-end" style="margin: 20px;">
                <button id="filter-btn" class="btn btn-success mr-2">
                    <i class="fa fa-filter"></i> Filter
                </button>
                <button id="clear-filter-btn" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="export-buttons">
        <button id="export-excel" class="btn btn-info">
            <i class="fa fa-file-excel-o"></i> Export to Excel
        </button>
        <button id="export-pdf" class="btn btn-danger">
            <i class="fa fa-file-pdf-o"></i> Export to PDF
        </button>
    </div>

    <!-- Add Subscription Modal -->
    <div class="modal fade" id="subscriptionModal" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionModalLabel">Add New Card Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="subscriptionForm">
                        @csrf
                        <div class="form-group">
                            <label for="user_location">Location</label>
                            <select name="user_location" id="user_location" class="form-control select2" required>
                                <option value="" disabled selected>Select Location</option>
                                @foreach ($userLocation as $item)
                                    <option value="{{$item->id}}">{{$item->location_name}} {{$item->city_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="patient_id">Patient</label>
                            <input type="hidden" id="card_screen" value="1">
                            <select name="patient_id" id="patient_id" class="form-control patient_id" required>
                                <option value="" disabled selected>Select Patient</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Apply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Subscription Modal -->
    <div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-labelledby="editSubscriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubscriptionModalLabel">Edit Card Subscription</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editSubscriptionForm">
                        @csrf
                        @method('POST')
                        <input type="hidden" id="edit_subscription_id" name="id">
                        
                        <div class="form-group">
                            <label for="edit_card_number">Card Number</label>
                            <input type="text" name="card_number" id="edit_card_number" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="edit_patient_id">Patient</label>
                            <select name="patient_id" id="edit_patient_id" class="form-control patient_id" required>
                                <option value="" disabled>Select Patient</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_is_active">Status</label>
                            <select name="is_active" id="edit_is_active" class="form-control" required>
                                <option value="1">Active</option>
                                <option value="0">Non-Active</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_subscription_date">Subscription Date</label>
                            <input type="date" name="subscription_date" id="edit_subscription_date" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="edit_expiry_date" class="form-control" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="portlet light portlet-fit portlet-datatable bordered mt-2">
        <div class="portlet-body">
            <div class="table-container">
                <table class="table table-bordered" id="table" style="width:100% !important">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Card Number</th>
                            <th>Patient</th>
                            <th>Patient Phone</th>
                            <th>Location</th>
                            <th>Subscription Date</th>
                            <th>Expiry Date</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="10" style="text-align:right">Total:</th>
                            <th id="total-amount"></th> 
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@if (session('print_invoice'))
    <script>
        const invoiceWindow = window.open('', '_blank');
        invoiceWindow.document.write(`{!! session('print_invoice') !!}`);
        invoiceWindow.document.close();
        invoiceWindow.focus();
        invoiceWindow.print();
    </script>
@endif
@endsection

@section('javascript')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>

<script>
    $(document).ready(function () {
        // Store DataTable reference in a variable
        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.card-subscription.index') }}",
                data: function (d) {
                    // Add filter parameters to the AJAX request
                    d.user_location_filter = $('#user_location_filter').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'card_number', name: 'card_subscriptions.card_number' },
                { data: 'patient.name', name: 'patient.name' },
                { data: 'patient.phone', name: 'patient.phone' },
                { data: 'locations.name', name: 'locations.name' },
                { data: 'subscription_date', name: 'card_subscriptions.subscription_date', render: function(data) {return data ? data.split(' ')[0] : ''; } },
                { data: 'expiry_date', name: 'card_subscriptions.expiry_date', render: function(data) {return data ? data.split(' ')[0] : ''; } },
                { data: 'created_at', name: 'card_subscription_details.created_at', render: function(data) { return data ? moment(data).format('YYYY-MM-DD') : ''; } },
                { data: 'updated_at', name: 'card_subscription_details.updated_at', render: function(data) { return data ? moment(data).format('YYYY-MM-DD') : ''; } },
                { 
                    data: 'is_active', 
                    name: 'card_subscriptions.is_active', 
                    render: function(data) { 
                        return data == 1 ? '<span class="badge" style="background-color:blue">Active</span>' : '<span class="badge" style="background-color:red">Inactive</span>';
                    }
                },
                { data: 'amount', name: 'card_subscription_details.amount' },
                {
                    data: '',
                    searchable: false
                },
            ],
            "columnDefs": [
                {
                    // For Responsive
                    className: 'control',
                    orderable: false,
                    searchable: false,
                    targets: 0
                },
                {
                    // Actions
                    targets: -1,
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return (
                            @if(Gate::allows('delete_edit_card_subscriptions'))
                                '<a href="javascript:;" class="item-edit btn btn-primary btn-sm" onclick="edit(' + full.id + ')">' +
                                'Edit' +
                                '</a> ' +
                                '<a href="javascript:;" class="item-edit btn btn-danger btn-sm" onclick="delete_item(' + full.id + ')">' +
                                'Delete' +
                                '</a>'
                            @else
                                '-'
                            @endif
                        );
                    }
                },
                {
                    "defaultContent": "-",
                    "targets": "_all"
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                
                // Calculate total for the 'amount' column (index 9)
                var total = api
                    .column(10, { page: 'current' })
                    .data()
                    .reduce(function(sum, value) {
                        return sum + (parseFloat(value) || 0);
                    }, 0);

                // Update footer with formatted total
                $('#total-amount').html(total.toFixed(2));
            }
        });

        // Filter Button Click
        $('#filter-btn').on('click', function () {
            table.draw();
        });

        // Clear Filter Button Click
        $('#clear-filter-btn').on('click', function () {
            $('#user_location_filter').val('');
            $('#from_date').val('');
            $('#to_date').val('');
            table.draw();
        });

        $('#export-excel').on('click', function() {
            var user_location_filter = $('#user_location_filter').val();
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var url = "{{ route('admin.card-subscription.export') }}?format=excel";
            if (fromDate) url += "&from_date=" + fromDate;
            if (toDate) url += "&to_date=" + toDate;
            console.log('Export URL:', url);
            window.location.href = url;
        });

        $('#export-pdf').on('click', function() {
            var user_location_filter = $('#user_location_filter').val();
            var fromDate = $('#from_date').val();
            var toDate = $('#to_date').val();
            var url = "{{ route('admin.card-subscription.export') }}?format=pdf";
            if (fromDate) url += "&from_date=" + fromDate;
            if (toDate) url += "&to_date=" + toDate;
            console.log('Export URL:', url);
            window.open(url, '_blank');
        });

        // Form submissions and other existing code...
        $('#editSubscriptionForm').submit(function (e) {
            e.preventDefault();
            let subscriptionId = $('#edit_subscription_id').val(); 

            $.ajax({
                url: "{{ url('admin/card-subscription') }}/" + subscriptionId,
                type: "POST",
                data: $(this).serialize() + "&_method=PATCH",
                success: function (response) {
                    $('#editSubscriptionModal').modal('hide');
                    location.reload();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert("Something went wrong!");
                }
            });
        });

        $(document).on('click', '.btn-edit-subscription', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            $('#edit_subscription_id').val($btn.data('id'));
            $('#edit_card_number').val($btn.data('card_number'));
            
            const patientId = $btn.data('patient_id');
            const patientName = $btn.data('patient_name');
            if (patientId) {
                const option = new Option(
                    `${patientName} - ${patientId}`, 
                    patientId, 
                    true, 
                    true
                );
                $('#edit_patient_id').empty().append(option).trigger('change');
            }
            
            $('#edit_is_active').val($btn.data('is_active'));
            $('#edit_subscription_date').val($btn.data('subscription_date'));
            $('#edit_expiry_date').val($btn.data('expiry_date'));
            
            $('#editSubscriptionModal').modal('show');
        });

        $('#subscriptionForm').submit(function (e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.card-subscription.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function (response) {
                    $('#subscriptionModal').modal('hide');
                    location.reload();
                },
                error: function (xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        });
    });

    function edit(id) {
        $.ajax({
            url: "{{ url('admin/card-subscription') }}/" + id + "/edit",
            type: "GET",
            success: function(response) {
                if (response) {
                    $('#edit_subscription_id').val(response.id);
                    $('#edit_card_number').val(response.card_number);

                    const patientId = response.patient_id;
                    const patientName = response.patient ? response.patient.name : 'N/A';
                    const option = new Option(`${patientName} - ${patientId}`, patientId, true, true);
                    $('#edit_patient_id').empty().append(option).trigger('change');

                    $('#edit_is_active').val(response.is_active);
                    $('#edit_subscription_date').val(formatDate(response.subscription_date));
                    $('#edit_expiry_date').val(formatDate(response.expiry_date));

                    $('#editSubscriptionModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert("Failed to fetch data!");
            }
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function delete_item(id) {
        if (confirm('Are you sure you want to delete this subscription?')) {
            $.ajax({
                url: "{{ url('admin/card-subscription') }}/" + id,
                type: "POST",
                data: {
                    _method: "DELETE",
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    alert('Subscription deleted successfully.');
                    location.reload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Failed to delete subscription.');
                }
            });
        }
    }
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/scripts/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/pages/scripts/components-date-time-pickers.min.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/reports/summary/general.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>
@endsection