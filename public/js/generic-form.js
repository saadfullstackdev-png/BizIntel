// Initialize Select2
$(document).ready(function() {
    $('.select2').each(function () {
        var $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
          placeholder: 'Select value',
          dropdownParent: $this.parent()
        });
    });

    $('.modal').on('hidden.bs.modal', function () {
        const $form = $(this).find('form');
        $form[0].reset();
        $form.find('input[name="_method"]').remove();
        $form.attr('action', '#');
        $form.find('select').val('').trigger('change');
        $('body').focus();
        $(this).blur();
    });
});

$(document).on("submit", "form.ajax-form", function (e) {
    e.preventDefault();
    let form = $(this);
    let modal = form.closest(".modal");
    let tableId = form.data("datatable") || '#dataTable';
    let formData = new FormData(this);

    blockUI();
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            unblockUI();
            form[0].reset();
            form.attr("action", '#');
            if (form.find('input[name="_method"]').length > 0) {
                form.find('input[name="_method"]').remove();
            }
            showToast("success", response.message);

            if (modal.length) {
                // $('body').focus();
                $(modal).find(':focus').blur();
                modal.modal("hide");
            }

            if (tableId) {
                $(tableId).DataTable().ajax.reload();
            }

            let redirectUrl = form.data("redirect");
            if (redirectUrl) {
                setTimeout(function () {
                    window.location.href = redirectUrl;
                }, 1000);
            }
        },
        error: function (jqXHR) {
            unblockUI();
            if (jqXHR.status === 422) {
                $.each(jqXHR.responseJSON.errors, function (index, value) {
                    showToast("error", value);
                });
            } else {
                showToast("error", "An error occurred! Please contact the administrator.");
            }
        },
    });
});


// Edit Record
$(document).on("click", ".editRecord", function (e) {
    e.preventDefault();
    blockUI();

    let rowId = $(this).data('id');
    let targetUrl = $(this).data("target-url");
    // let modalTitle = $(this).data("title") || "Edit Record";
    let modalTitle = $(this).attr('title') || "Edit Record";
    let formSelector = $(this).data("form") || "form";
    let modalSelector = $(this).data("modal") || "#addModal";
    // let fields = $(this).data("fields");
    // let formAction = $(this).data("form-action");

    $.get(targetUrl + '/' + rowId +'/edit', function (response) {
        unblockUI();

        $('#modelHeading').text(modalTitle);

        let $form = $(formSelector);
        $form.attr('action', targetUrl + '/' + rowId);

        // if ($form.find('input[name="_method"]').length === 0) {
            $form.append('<input type="hidden" name="_method" value="PUT">');
        // }

        if (response.fields) {
            response.fields.forEach(function (field) {
                let name = field.trim();
                let value = response.data[name];
                let $elements = $form.find(`[name="${name}"], [name="${name}[]"]`);

                $elements.each(function () {
                    let $el = $(this);
                    let type = $el.attr('type');

                    if (type === 'file') {
                        return; // Skip file inputs
                    }

                    if ($el.is('select')) {
                        // if ($el.hasClass('select2')) {
                        //     $el.val(value).select2().trigger('change');
                        // } else {
                            $el.val(value).trigger('change');
                        // }
                    } else if ($el.is('textarea')) {
                        $el.text(value || '');
                    } else if (type === 'radio') {
                        $el.prop('checked', $el.val() == value);
                    } else if (type === 'checkbox') {
                        if (Array.isArray(value)) {
                            $el.prop('checked', value.includes($el.val()));
                        } else {
                            $el.prop('checked', !!value);
                        }
                    } else {
                        $el.val(value || '');
                    }
                });
            });
        }
        $(modalSelector).modal('show');
    });
});

// Delete Record
$(document).on("click", ".deleteRecord", function (e) {
    e.preventDefault();
    let url = $(this).data("url");
    let tableId = $(this).data("datatable") || '#dataTable';
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
          cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            blockUI();
            $.ajax({
                url: url,
                type: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    unblockUI();
                    showToast("success", response.message);

                    if (tableId) {
                        $(tableId).DataTable().ajax.reload();
                    }
                },
                error: function (jqXHR) {
                    unblockUI();
                    showToast("error", "An error occurred! Please contact the administrator.");
                },
            });
        }
    });
});
