var FormValidation = function () {
    var e = function () {
        var e = $("#status-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore:":not(:visible)",
            messages: {
            },
            rules: {
                appointment_status_id: {required: !0},
                cancellation_reason_id: {required: !0},
                reason: {required: !0},
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show()
            },
            errorPlacement: function (e, r) {
                var i = $(r).parent(".input-group");
                i.size() > 0 ? i.after(e) : r.after(e)
            },
            highlight: function (e) {
                $(e).closest(".form-group").addClass("has-error")
            },
            unhighlight: function (e) {
                $(e).closest(".form-group").removeClass("has-error")
            },
            success: function (e) {
                e.closest(".form-group").removeClass("has-error")
            },
            submitHandler: function (e) {
                i.show(), r.hide();
                $('#appointment_status_btn').attr('disabled',true);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: route('admin.appointments.storeappointmentstatus'),
                    type: "PUT",
                    data: $("#status-validation").serialize(),
                    cache: false,
                    success: function(response) {
                        console.log(response);
                        if(response.status == '1') {
                            $('.alert-success').html("Form is submitted successfully!");
                            $('#modal-footer').remove();
                            $('#appointment' + $('#appointment').val()).html($("#appointment_status_id option:selected").text());
                            console.log('Step 01');
                            setTimeout(function() {
                                $('#closeBtn').click();
                                console.log('Step 02');
                            }, 1000);
                        } else {
                            $('#appointment_status_btn').removeAttr('disabled');
                        }
                    }
                });
                return false;
            }
        })
    }
    return {
        init: function () {
            e()
        }
    }
}();
jQuery(document).ready(function () {
    FormValidation.init()
});