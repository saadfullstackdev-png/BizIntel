@extends('layouts.app')

@section('content')

<h1>Subscription Charges</h1>
@if(Gate::allows('add_subscription_charges'))
    <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#subscriptionChargeModal" id="openCreateModal">
        Add Charge
    </button>
@endif

<div class="portlet light portlet-fit portlet-datatable bordered mt-2" style="margin-top: 10px">
    <div class="portlet-body">
        <div class="table-container">
            <table id="subscription-charges-table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Categories</th>
                        <th>Discounts</th>
                        <th>Banner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="subscriptionChargeModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Subscription Charge</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="subscriptionChargeForm">
                    @csrf
                    <input type="hidden" name="id" id="charge_id">

                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" name="amount" id="amount" class="form-control" required>
                    </div>

                    <div id="repeater">
                        <div class="repeater-item">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="category_id">Category</label>
                                    <select name="category_id[]" id="category_id" class="form-control select2">
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="offered_discount">Offered Discount (%)</label>
                                    <input type="number" name="offered_discount[]" class="form-control" required>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                        </div>
                    </div>

                    <button type="button" id="add-item" class="btn btn-success btn-sm" style="margin-top: 10px">Add Another</button>

                    <div class="form-group" style="margin-top: 10px">
                        <label for="banner">Banner</label>
                        <input type="text" name="banner" id="banner" class="form-control" required>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        var table = $('#subscription-charges-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.subscription-charges.index') }}",
            columns: [
                { data: 'id', name: 'id', searchable: false, orderable: false },
                { data: 'amount', name: 'amount' },
                { data: 'categories', name: 'categories', orderable: false, searchable: false },
                { data: 'discounts', name: 'discounts', orderable: false, searchable: false },
                { data: 'banner', name: 'banner' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });

        $('#openCreateModal').click(function (e) {
            e.preventDefault()
            $('#modalTitle').text('Add Subscription Charge');
            $('#subscriptionChargeForm')[0].reset();
            $('#charge_id').val('');
            // $('#subscriptionChargeModal').modal('show');
        });

        $('#subscriptionChargeForm').submit(function (e) {
            e.preventDefault();

            let id = $('#charge_id').val();  // Ensure ID is retrieved correctly
            let url = id
                ? "{{ url('admin/subscription-charges') }}/" + id
                : "{{ url('admin/subscription-charges') }}";

            let formData = $(this).serialize();
            if (id) {
                formData += '&_method=PUT';  // Laravel requires _method=PUT for updates
            }

            $.ajax({
                url: url,
                type: 'POST',  // Always use POST to bypass the PUT restriction
                data: formData,
                success: function (response) {
                    $('#subscriptionChargeModal').modal('hide');
                    table.ajax.reload();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert('Something went wrong!');
                }
            });
        });
   
        $(document).on('click', '.edit-button', function () {
            let id = $(this).data('id');

            $.get("{{ route('admin.subscription-charges.index') }}/" + id + "/edit", function (data) {
                $('#modalTitle').text('Edit Subscription Charge');
                $('#charge_id').val(data.charge.id);
                $('#amount').val(data.charge.amount);
                $('#banner').val(data.charge.banner);

                $('#repeater').html('');

                // Loop through categories and append to modal
                data.charge.categories.forEach(category => {
                    let newItem = `
                        <div class="repeater-item">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="category_id">Category</label>
                                    <select name="category_id[]" class="form-control select2">
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}" ${category.id == {{ $id }} ? 'selected' : ''}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="offered_discount">Offered Discount (%)</label>
                                    <input type="number" name="offered_discount[]" class="form-control" value="${category.pivot.offered_discount * 100}" required>
                                </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                        </div>
                    `;

                    $('#repeater').append(newItem);
                });

                $('#subscriptionChargeModal').modal('show');
            }).fail(function () {
                alert('Error loading data.');
            });
        });


        // Add & Remove Category Discount Rows
        $('#add-item').click(function () {
            let newItem = $('.repeater-item:first').clone();
            newItem.find('input, select').val('');
            $('#repeater').append(newItem);
        });

        $(document).on('click', '.remove-item', function () {
            if ($('.repeater-item').length > 1) {
                $(this).closest('.repeater-item').remove();
            } else {
                alert('You need at least one item.');
            }
        });

    });
</script>

@endsection
