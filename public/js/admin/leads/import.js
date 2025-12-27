var FormValidation = function () {
    var e = function () {
        var e = $("#form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
            },
            rules: {
                leads_file: {required: !0},
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show(), App.scrollTo(r, -200)
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
                i.show(), r.hide()
                return true;
            }
        });

        $('#update_records').click(function (e) {
           if($('#update_records:checked').length) {
               $('#skip_lead_statuses').parent().removeClass('mt-checkbox-disabled');
               $('#skip_lead_statuses').removeAttr('disabled');
           } else {
               $('#skip_lead_statuses').parent().addClass('mt-checkbox-disabled');
               $('#skip_lead_statuses').attr('disabled', true);
           }
        });
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