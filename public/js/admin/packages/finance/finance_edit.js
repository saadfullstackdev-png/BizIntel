var FinanceFormValidation = function () {
    var e = function () {
        var e = $("#finance-form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore:":not(:visible)",
            messages: {
            },
            rules: {
                payment_mode_id: {required: !0},
                cash_amount: {required: !0},
                created_at: {required: !0},
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
                i.hide(), r.hide();
                $('#finance_save_btn').attr('disabled',true);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: route('admin.packages.edit_cash.store'),
                    type: "PUT",
                    data: $("#finance-form-validation").serialize(),
                    cache: false,
                    success: function(response) {
                        if(response.status == '1') {
                            if(response.amount_status == '1'){
                                $('#alert-success').html("Record is updated successfully!").show();
                            } else {
                                $('#alert-success').html("Record is updated successfully except cash amount!").show();
                            }
                            location.reload();
                        } else {
                            $('#finance_save_btn').removeAttr('disabled');
                            $('#alert-danger').html("Something went wrong!").show();
                        }
                    }
                });
                return false;
            }
        })
    }
    return {
        init: function () {
            e();
        }
    }
}();
jQuery(document).ready(function () {

    FinanceFormValidation.init();

    $('.date_to_rota').datepicker({
        format: 'yyyy-mm-dd',
    }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    })
    $('.select2_finance_edit').select2({width: '100%'});
});