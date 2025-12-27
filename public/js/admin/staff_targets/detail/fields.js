var CreateFormValidation = function () {
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
                name: {required: !0},
                price: {required: !0},
                total_services: {required: !0},
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
            submitHandler: function (event) {
                i.show(), r.hide();
                $("input[type=submit]", e).attr('disabled', true);

                x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
                    if (response.status == '1') {
                        r.hide();
                        i.html(response.message);
                        window.location = route('admin.staff_targets.detail',[response.location_id]);
                    } else {
                        $("input[type=submit]", e).removeAttr('disabled');
                        i.hide();
                        r.html(response.message);
                        r.show();
                    }
                });
                return false;
            }
        })
        $('.form-control.inpt-focus').focus();

    }

    var x = function (action, method, data, callback) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: action,
            type: method,
            data: data,
            cache: false,
            success: function (response) {
                if (response.status == '1') {
                    callback({
                        'status': response.status,
                        'location_id': response.location_id,
                        'message': response.message,
                    });
                } else {
                    callback({
                        'status': response.status,
                        'location_id': response.location_id,
                        'message': response.message.join('<br/>'),
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (xhr.status == '401') {
                    callback({
                        'status': 0,
                        'location_id': 0,
                        'message': 'You are not authorized to access this resouce',
                    });
                } else {
                    callback({
                        'status': 0,
                        'location_id': 0,
                        'message': 'Unable to process your request, please try again later.',
                    });
                }
            }
        });
    }

    var calculateTargetAmount = function () {
        var total_amount = 0;
        $('.target_amount').each(function (index, value) {
            if($(this).val() != '') {
                total_amount = total_amount + parseFloat($(this).val());
            }
        });
        $('#total_amount').val(total_amount);
    }

    var calculateTargetServices = function () {
        var total_services = 0;
        $('.target_services').each(function (index, value) {
            if($(this).val() != '') {
                total_services = total_services + parseFloat($(this).val());
            }
        });
        $('#total_services').val(total_services);
    }

    var loadEndServices = function () {
        $('#staff_target_error').hide();
        $('#staff_target_zero').hide();
        $(".alert-danger").hide();
        $('#save_btn').hide();

        var year = $('#year').val();
        var month = $('#month').val();
        var location_id = $('#location_id').val();
        var staff_id = $('#staff_id').val();

        if(year == '' || month == '' || location_id == '' || staff_id == '') {
           $('#staff_target_error').show();
           return false;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.staff_targets.load_target_services'),
            type: 'POST',
            data: {
                year: year,
                month: month,
                staff_id: staff_id,
                location_id: location_id
            },
            cache: false,
            success: function(response) {
                if(response.status == '1') {
                    $('#table_services').html(response.table_content);
                    if(response.target_services_count == '0') {
                        $('#staff_target_zero').show();
                    } else {
                        $('#save_btn').show();
                        calculateTargetAmount();
                        calculateTargetServices();
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {

            }
        });
    }

    return {
        init: function () {
            e();
        },
        loadEndServices: loadEndServices,
        calculateTargetAmount: calculateTargetAmount,
        calculateTargetServices: calculateTargetServices,
    }
}();
jQuery(document).ready(function () {
    CreateFormValidation.init();
    $('#staff_id').select2({ width: '100%' }).on('change', function (e) {
        CreateFormValidation.loadEndServices();
    });
});