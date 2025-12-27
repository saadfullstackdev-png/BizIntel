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
                        window.location = route('admin.centre_targets.index');
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
        console.log("I am ghere");
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
                        'message': response.message,
                    });
                } else {
                    callback({
                        'status': response.status,
                        'message': response.message.join('<br/>'),
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (xhr.status == '401') {
                    callback({
                        'status': 0,
                        'message': 'You are not authorized to access this resouce',
                    });
                } else {
                    callback({
                        'status': 0,
                        'message': 'Unable to process your request, please try again later.',
                    });
                }
            }
        });
    }
    var loadActiveLocation = function () {
        var year = $('#year').val();
        var month = $('#month').val();

        if(year == '' || month == '') {
            $('#centre_require_field').show();
            return false;
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.centre_targets.load_target_centre'),
            type: 'POST',
            data: {
                year: year,
                month: month,
            },
            cache: false,
            success: function(response) {
                if(response.status == '1') {
                    $('#centre_require_field').hide();
                    $('#table_location').html(response.target_location);
                    $('#working_days').val(response.center_target_working_days);
                    $('#save_btn').show();
                    if(response.center_target_status == 0){
                        $('#centre_edit_perform').hide();
                     } else {
                        $('#centre_edit_perform').show();
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
        loadActiveLocation: loadActiveLocation,
    }
}();
jQuery(document).ready(function () {
    CreateFormValidation.init();
});