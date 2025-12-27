@extends('layouts.app')
@section('stylesheets')
<style>
    .select2-selection {
        border: 1px solid black !important;
        padding: 5px !important;
    }
</style>
@section('content')
    <h1>Purchased Services</h1>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Add New Subscription Button -->
    {{-- <div class="mb-3" style="margin-bottom: 10px">
        <button class="btn btn-primary" data-toggle="modal" data-target="#subscriptionModal">Add New Subscription</button>
    </div> --}}

    <!-- Add Subscription Modal -->
    {{-- <div class="modal fade" id="subscriptionModal" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true">
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
                            <label for="patient_id">Patient</label>
                            <select name="patient_id" id="patient_id" class="form-control patient_id" required>
                                <option value="" disabled selected>Select Patient</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Apply</button>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- Add this to your index.blade.php file -->

    <!-- Edit Subscription Modal -->
    {{-- <div class="modal fade" id="editSubscriptionModal" tabindex="-1" aria-labelledby="editSubscriptionModalLabel" aria-hidden="true">
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
    </div> --}}


    <!-- Subscriptions Table -->
    <div class="portlet light portlet-fit portlet-datatable bordered mt-2">
        <div class="portlet-body">
            <div class="table-container">
                <table class="table table-bordered" id="table" style="width:100% !important">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Services</th>
                            <th>Location</th>
                            <th>Patient</th>
                            <th>Phone</th>
                            <th>Consumed</th>
                            <th>Amount</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
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
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('admin/purchased_serivces') }}",
                columns: [
                    { data: null, name: 'counter' },
                    { data: 'service_name', name: 'services.name' },
                    { data: 'location_name', name: 'locations.name' },
                    { data: 'patient_name', name: 'users.name' },
                    { data: 'patient_phone', name: 'users.phone' },
                    { data: 'is_consumed', name: 'is_consumed' },
                    { data: 'purchased_services_price', name: 'purchased_services.price' },
                    { data: 'created_at', name: 'purchased_services.created_at' },
                ],
                "columnDefs": [
                    {
                        targets: 0, // First column for row numbers
                        orderable: false,
                        searchable: false,
                        render: function (data, type, full, meta) {
                            return meta.row + 1; // Generates 1, 2, 3, ...
                        }
                    },
                    {
                        targets: 5, // Column index for 'is_consumed'
                        render: function (data, type, full, meta) {
                            return full.is_consumed == 1 ? 'Yes' : 'No'; // Convert 1 -> "Yes", 0 -> "No"
                        }
                    },
                    {
                        // For Responsive
                        className: 'control',
                        orderable: false,
                        searchable: false,
                        targets: 0
                    },
                    {
                        "defaultContent": "-",
                        "targets": "_all"
                    }
                ],
            });
        $('#editSubscriptionForm').submit(function (e) {
            console.log(1221);

            e.preventDefault();

            let subscriptionId = $('#edit_subscription_id').val(); 

            $.ajax({
                url: "{{ url('admin/card-subscription') }}/" + subscriptionId, // Correct Laravel route
                type: "POST", // Laravel expects PATCH, so we override it
                data: $(this).serialize() + "&_method=PATCH", // Append _method=PATCH
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
            
            // Get data from button attributes
            const $btn = $(this);
            $('#edit_subscription_id').val($btn.data('id'));
            $('#edit_card_number').val($btn.data('card_number'));
            
            // Set patient select
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
            
            // Show the modal
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
                console.log("Response received:", response.subscription_date);
                if (response) {
                    $('#edit_subscription_id').val(response.id);
                    $('#edit_card_number').val(response.card_number);

                    // Set patient select
                    const patientId = response.patient_id;
                    const patientName = response.patient ? response.patient.name : 'N/A';
                    const option = new Option(`${patientName} - ${patientId}`, patientId, true, true);
                    $('#edit_patient_id').empty().append(option).trigger('change');

                    $('#edit_is_active').val(response.is_active);
                    $('#edit_subscription_date').val(formatDate(response.subscription_date));
                    $('#edit_expiry_date').val(formatDate(response.expiry_date));

                    // Show modal
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
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Ensure two-digit format
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`; // Adjust format as needed (e.g., `${day}-${month}-${year}`)
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
{{-- <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
    type="text/javascript"></script> --}}
<script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
    type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL SCRIPTS -->
{{-- <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
    type="text/javascript"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- END PAGE LEVEL SCRIPTS -->
<script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/scripts/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/pages/scripts/components-date-time-pickers.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('js/admin/reports/summary/general.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>
@endsection