var FormValidation = function () {
    var e = function () {
        var form_submit = 0;
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
                parent_id: {required: !0},
                duration: {required: !0},
                price: {required: '#price:visible', number: true, min: 0},
                file: {extension: "jpeg|jpg|png|gif"},
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

            submitHandler: function (evl) {
                i.show(), r.hide();
                $("input[type=submit]", e).attr('disabled', true);
                if(!form_submit) {
                    x(route('admin.services.verify_edit'), 'PUT', e.serialize(), function (response) {
                        if (response.status == '1') {
                            r.hide();
                            i.html(response.message);
                            form_submit = 1;
                            e.submit();
                        } else {
                            $("input[type=submit]", e).removeAttr('disabled');
                            i.hide();
                            r.html(response.message);
                            r.show();
                            form_submit = 0;
                        }
                    });
                    return false;
                } else {
                    return true;
                }
            }
        });
        $('.form-control.inpt-focus').focus();
        $('#end_node').click(function () {
            eN();
            im();
            ct();
        });
        // By default hide all end node fields
        eN();
        im();
        ct();

        $('#complimentory').click(function () {
            com();
        });
        com();
    }

    var eN = function checkNode() {
        $('.end_node').hide();
        if ($('#end_node:checked').length) {
            $('.end_node').show();
        }
    }

    var com = function ComplimentoryNode() {
        if ($('#complimentory:checked').length) {
            $('#price').val('0');
            $('#price').prop("readonly", true);
        } else {
            $('#price').prop( "readonly", false );
        }
    }

    var im = function IsMobile() {
        if ($('#end_node:checked').length) {
            $('.is_mobile').hide();
        } else {
            $('.is_mobile').show();
        }
    }

    var ct = function ConsultancyType() {
        if ($('#end_node:checked').length) {
            $('.consultancy_type').hide();
        } else {
            $('.consultancy_type').show();
        }
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

    return {
        init: function () {
            e()
        }
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({ width: '100%' });
});