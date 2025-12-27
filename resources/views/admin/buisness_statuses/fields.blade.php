$(function () {
    var $form = $('#form-create, #form-edit');

    $form.validate({
        rules: { name: 'required' },
        submitHandler: function (form) {
            var $btn = $(form).find('button[type=submit]').prop('disabled', true);
            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: $(form).serialize(),
                success: function (res) {
                    if (res.status === 1) {
                        $('#ajax_modal').modal('hide');
                        $('#datatable_ajax').DataTable().ajax.reload(null, false);
                        toastr.success(res.message);
                    } else {
                        toastr.error(Array.isArray(res.message) ? res.message.join('<br>') : res.message);
                    }
                },
                complete: function () { $btn.prop('disabled', false); }
            });
            return false;
        }
    });
});