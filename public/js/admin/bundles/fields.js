var FormValidation = function () {
    var e = function () {
        var e = $("#form-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {},
            rules: {
                name: {required: !0},
                price: {required: !0},
                total_services: {required: !0},
                tax_treatment_type_id: {required: !0},
                is_mobile: {required: !0},
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
            // submitHandler: function (event) {
            //     i.show(), r.hide();
            //     $("input[type=submit]", e).attr('disabled', true);
            //
            //     x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
            //         if (response.status == '1') {
            //             r.hide();
            //             i.html(response.message);
            //             window.location = route('admin.bundles.index');
            //         } else {
            //             $("input[type=submit]", e).removeAttr('disabled');
            //             i.hide();
            //             r.html(response.message);
            //             r.show();
            //         }
            //     });
            //     return false;
            // }
        })
        $('.form-control.inpt-focus').focus();

    }

    // var x = function (action, method, data, callback) {
    //     $.ajax({
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         url: action,
    //         type: method,
    //         data: new formDate(data),
    //         cache: false,
    //         success: function (response) {
    //             if (response.status == '1') {
    //                 callback({
    //                     'status': response.status,
    //                     'message': response.message,
    //                 });
    //             } else {
    //                 callback({
    //                     'status': response.status,
    //                     'message': response.message.join('<br/>'),
    //                 });
    //             }
    //         },
    //         error: function (xhr, ajaxOptions, thrownError) {
    //             if (xhr.status == '401') {
    //                 callback({
    //                     'status': 0,
    //                     'message': 'You are not authorized to access this resouce',
    //                 });
    //             } else {
    //                 callback({
    //                     'status': 0,
    //                     'message': 'Unable to process your request, please try again later.',
    //                 });
    //             }
    //         }
    //     });
    // }

    var total_services = parseInt($('#total_servicesCount').val());
    var counter = 1000;

    var addRow = function () {
        if ($('#service_id').val() != '') {
            // Add count on row
            counter = counter + 1;

            var singleRow = $('#rowGenerator').html();
            singleRow = singleRow.replace(/AAA/g, counter);
            singleRow = singleRow.replace(/BBB/g, '');
            $('#table_services').append(singleRow);


            $("#serviceID" + counter).val($('#service_id').find(':selected').attr('data-id'));
            $("#serviceText" + counter).html($('#service_id').find(':selected').attr('data-name'));
            $("#servicePriceValue" + counter).val($('#service_id').find(':selected').attr('data-price'));
            $("#servicePrice" + counter).html($('#service_id').find(':selected').attr('data-price'));

            // Increment Total Services count
            total_services = total_services + 1;
            $('#total_services').val(total_services);
            $('#total_servicesCount').val(total_services);

            calculateServicesTotal();
        }
    }

    var calculateServicesTotal = function () {
        var totalPrice = 0;
        $('.servicePriceValue').each(function (index, value) {
            totalPrice = totalPrice + parseFloat($(this).val());
        });
        $('#services_price').val(totalPrice);
    }

    var deleteRow = function (id) {
        if (confirm('Are you sure to delete')) {

            $("#singleRow" + id).remove();

            // Decrement Total Services count
            total_services = total_services - 1;
            $('#total_services').val(total_services);
            $('#total_servicesCount').val(total_services);

            calculateServicesTotal();
        }
    }

    return {
        init: function () {
            e();
        },
        addRow: addRow,
        deleteRow: deleteRow,
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({width: '100%'});
});