{{-- <div class="modal fade" id="ajax" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content"> --}}
            <div class="modal-header">
                <button type="button" id="closeBtn" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Update Goldern Ticket Status</h4>
            </div>

            {!! Form::open([
                'id' => 'buisness-status-form',
                'route' => ['admin.appointments.storebuisnessstatus', $appointment->id],
                'method' => 'POST',
                'class' => 'form-horizontal'
            ]) !!}

            <div class="modal-body">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button class="close" data-close="alert"></button>
                        Please fix the errors below.
                    </div>
                    <div class="alert alert-success display-hide">
                        <button class="close" data-close="alert"></button>
                        Updating...
                    </div>

                    {!! Form::hidden('id', $appointment->id) !!}

                    <div class="form-group">
                        <label class="col-md-4 control-label">Goldern Ticket Status <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                            {!! Form::select('buisness_status_id', $buisness_statuses, $appointment->buisness_status_id, [
                                'class' => 'form-control select2',
                                'placeholder' => 'Select Business Status',
                                'required' => 'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" id="modal-footer">
                <button type="button" class="btn default" data-dismiss="modal">Close</button>
                <button type="submit" id="buisness_status_btn" class="btn btn-success">
                    {{ trans('global.app_save') }}
                </button>
            </div>

            {!! Form::close() !!}
        {{-- </div>
    </div>
</div> --}}

<script type="text/javascript">
    $(document).ready(function () {
        // Re-init Select2 every time modal opens
        $('#ajax').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#ajax'),
                width: '100%'
            });
        });

        var form = $('#buisness-status-form');
        var successAlert = form.find('.alert-success');
        var errorAlert = form.find('.alert-danger');

        // Destroy previous validation
        if (form.data('validator')) {
            form.data('validator').destroy();
        }

        form.validate({
            errorElement: 'span',
            errorClass: 'help-block help-block-error',
            focusInvalid: false,
            rules: {
                buisness_status_id: { required: true }
            },
            highlight: function (element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('.form-group').removeClass('has-error');
            },
            errorPlacement: function (error, element) {
                element.closest('.form-group').append(error);
            },
            submitHandler: function (f) {
                successAlert.show();
                errorAlert.hide();
                $('#buisness_status_btn').attr('disabled', true);

                $.ajax({
                    url: f.action,  // ← THIS FIXES THE URL (includes /1/buisness-status)
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if (res.status == 1) {
                            // Find the correct <a> tag in the table (by appointment ID in href or text)
                            var $link = $('a[href*="/appointments/' + '{{ $appointment->id }}' + '/buisness-status"]');
                            if ($link.length) {
                                $link.text(res.buisness_status_name);
                            }

                            toastr.success('Business status updated successfully!');
                            setTimeout(function () {
                                $('#closeBtn').click();
                            }, 800);
                        } else {
                            errorAlert.html('Update failed. Try again.').show();
                            $('#buisness_status_btn').removeAttr('disabled');
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Server error';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        errorAlert.html(msg).show();
                        $('#buisness_status_btn').removeAttr('disabled');
                    },
                    complete: function () {
                        successAlert.hide();
                    }
                });

                return false;
            }
        });
    });
</script>