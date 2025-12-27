$(document).ready(function () {
    /*on Change when location drop down trigger and get related package and service*/
    $(document).on('change', '#location_id_1', function () {

        $('#inputfieldMessage').hide();
        $('#datanotexist').hide();
        $('#wrongMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var location_id = $('#location_id_1').val();
        var div = $(this).parents();
        var op = " ";

        $("#service_id_1").val('0').trigger('change')

        if (location_id) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.getservice'),
                data: {
                    'location_id': location_id
                },
                success: function (resposne) {
                    $('#parent_id_1').change();
                    if (resposne.status == '1') {
                        op += '<option value="0" selected disabled>Select Service</option>';
                        for (var i = 0; i < resposne.service.length; i++) {
                            op += '<option value="' + resposne.service[i].id + '">' + resposne.service[i].name + '</option>';
                        }
                        div.find('.service_id_1').html("");
                        div.find('.service_id_1').append(op);
                    } else {
                        $('#datanotexist').show();
                    }
                },
            });
        }
    });

    /*Only use in appointment plans start*/
    $('#location_id_1').change();
    /*Only use in appointment plans end*/

    /*Change when patient id change*/
    $(document).on('change', '#parent_id_1', function () {
        $("#service_id_1").val('0').trigger('change')
    });

    /*Change when appointment id change*/
    $(document).on('change', '#appointment_id_1', function () {

        var appointment_id_1 = $('#appointment_id_1').val().split(".");

        if (appointment_id_1[1] == 'D') {
            $("#lead_source_id_1").prop("disabled", false);
        } else {
            $("#lead_source_id_1").val('').trigger('change');
            $("#lead_source_id_1").prop("disabled", true);
        }
    });

    /*Change when service drop down trigger*/
    $(document).on('change', '#service_id_1', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var service_id = $('#service_id_1').val();
        var patient_id = $('#client_id').val();
        var location_id = $('#location_id_1').val();

        var div = $(this).parents();
        var op = " ";

        $("#discount_id_1").val('0').trigger('change')

        if (service_id && patient_id) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.getserviceinfo'),
                data: {
                    'bundle_id': service_id, //Basically it is bundle id
                    'location_id': location_id,
                    'patient_id': patient_id
                },
                success: function (resposne) {
                    var count = 0;
                    if (resposne.status == '1') {
                        op += '<option value="0" selected>Select Discount</option>';
                        $.each(resposne.discounts, function (index, obj) {
                            op += '<option value="' + obj.id + '">' + obj.name + '</option>';
                        });
                        div.find('.discount_id_1').html("");
                        div.find('.discount_id_1').append(op);

                        $("#net_amount_1").val(resposne.net_amount);
                        $("#net_amount_1").prop("disabled", true);

                    } else {

                        op += '<option value="0" selected>Select Discount</option>';
                        div.find('.discount_id_1').html("");
                        div.find('.discount_id_1').append(op);

                        $("#net_amount_1").val(resposne.net_amount);
                        $("#net_amount_1").prop("disabled", true);

                    }
                },
            });
        }
    });

    /*Change when patient id change*/
    $(document).on('change', '#parent_id_1', function () {

        $('#inputfieldMessage').hide();

        var location_id = $('#location_id_1').val();
        var patient_id = $('#client_id').val();

        $("#service_id_1").val('0').trigger('change')
        console.log("patient id " + patient_id);
        if (location_id) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.getappointmentinfo'),
                data: {
                    'patient_id': patient_id,
                    'location_id': location_id
                },
                success: function (resposne) {
                    if (resposne.status) {
                        op = '';
                        op += '<option value="" >Select Appointment</option>';
                        jQuery.each(resposne.data, function (i, data) {
                            op += '<option value="' + resposne.data[i].id + '">' + resposne.data[i].name + '</option>';
                        });
                        $('.appointment_id_1').html("");
                        $('.appointment_id_1').append(op);

                        $("#appointment_id_1").val($("#appointment_id_1 option:nth-child(2)").val());

                    } else {
                        op = '';
                        op += '<option value="0" selected disabled>Select Appointment</option>';
                        $('.appointment_id_1').html("");
                        $('.appointment_id_1').append(op);
                    }
                },
            });
        } else {
            $('#inputfieldMessage').show();
        }

    });

    /*Change function when discount drop down trigger*/
    $(document).on('change', '#discount_id_1', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var service_id = $('#service_id_1').val(); //Basicailly it is bundle id
        var discount_id = $(this).val();
        var random_id = $('#random_id_1').val();
        var unique_id = $('#unique_id_1').val();

        var div = $(this).parents();
        var op = " ";

        if (service_id == null && discount_id == null) {
            $("#reference_id_1").prop("disabled", true);
            $("#reference_id_1").val('0').trigger('change');
            $("#discount_type_1").prop("disabled", false);
            $("#discount_type_1").val('').trigger('change');
            $("#discount_value_1").prop("disabled", false);
            $("#discount_value_1").val('');
            $("#net_amount_1").prop("disabled", false);
            $("#net_amount_1").val('');
            $("#slug_1").val('not_custom');

            op += '<option value="0" selected>Select Discount</option>';
            div.find('.discount_id_1').html("");
            div.find('.discount_id_1').append(op);

        } else if (discount_id == null && service_id != null) {
            $("#reference_id_1").prop("disabled", true);
            $("#reference_id_1").val('0').trigger('change');
            $("#discount_type_1").prop("disabled", true);
            $("#discount_type_1").val('').trigger('change');
            $("#discount_value_1").prop("disabled", true);
            $("#discount_value_1").val('');
            $("#slug_1").val('not_custom');

        } else if (service_id == null && discount_id == '0') {

            $("#discount_type_1").prop("disabled", false);
            $("#discount_type_1").val('').trigger('change');
            $("#discount_value_1").prop("disabled", false);
            $("#discount_value_1").val('');
            $("#net_amount_1").prop("disabled", false);
            $("#net_amount_1").val('');
            $("#slug_1").val('not_custom');

            op += '<option value="0" selected>Select Discount</option>';
            div.find('.discount_id_1').html("");
            div.find('.discount_id_1').append(op);

        } else if (service_id && discount_id == '0') {
            $("#slug_1").val('not_custom');
            $.ajax({
                type: 'get',
                url: route('admin.packages.getserviceinfo_discount_zero'),
                data: {
                    'bundle_id': service_id, //Basicailly it is bundle id
                },
                success: function (resposne) {
                    if (resposne.status == '1') {
                        $("#reference_id_1").prop("disabled", true);
                        $("#reference_id_1").val('0').trigger('change');
                        $("#discount_type_1").prop("disabled", true);
                        $("#discount_type_1").val('').trigger('change');
                        $("#discount_value_1").prop("disabled", true);
                        $("#discount_value_1").val('');
                        $("#net_amount_1").val(resposne.net_amount);
                        $("#net_amount_1").prop("disabled", true);
                    } else {
                        $('#wrongMessage').show();
                    }
                },
            });
        } else {
            if (service_id && discount_id != '0') {
                var ref = " ";
                $.ajax({
                    type: 'get',
                    url: route('admin.packages.getdiscountinfo'),
                    data: {
                        'service_id': service_id,
                        'discount_id': discount_id,
                        'random_id': random_id,
                        'unique_id': unique_id
                    },
                    success: function (resposne) {
                        if (resposne.status == '1') {
                            if (resposne.slug == 'default') {
                                $("#reference_id_1").prop("disabled", true);
                                $("#reference_id_1").val('0').trigger('change');
                                $("#discount_type_1").val(resposne.discount_type).change();
                                $("#discount_type_1").prop("disabled", true);
                                $("#discount_value_1").val(resposne.discount_price);
                                $("#discount_value_1").prop("disabled", true);
                                $("#net_amount_1").val(resposne.net_amount);
                                $("#net_amount_1").prop("disabled", true);
                                $("#slug_1").val('not_custom');

                            } else if (resposne.slug == 'periodic') {

                                ref += '<option value="0" selected>Select Reference</option>';
                                $.each(resposne.references, function (index, obj) {
                                    ref += '<option value="' + obj.id + '">' + obj.name + '</option>';
                                });
                                div.find('.reference_id_1').html("");
                                div.find('.reference_id_1').append(ref);

                                $("#reference_id_1").prop("disabled", false);

                                $("#discount_type_1").prop("disabled", true);
                                $("#discount_type_1").val('').trigger('change');

                                $("#discount_value_1").prop("disabled", true);
                                $("#discount_value_1").val('');

                                $("#net_amount_1").val(resposne.net_amount);
                                $("#net_amount_1").prop("disabled", true);

                            } else {
                                $("#reference_id_1").prop("disabled", true);
                                $("#reference_id_1").val('0').trigger('change');
                                $("#discount_type_1").prop("disabled", false);
                                $("#discount_type_1").val('').trigger('change');
                                $("#discount_value_1").prop("disabled", false);
                                $("#discount_value_1").val('');
                                $("#net_amount_1").prop("disabled", true);
                                $("#net_amount_1").val('');
                                $("#slug_1").val('custom');
                            }
                        } else {
                            $('#wrongMessage').show();
                        }
                    },
                });
            }
        }
    });

    /*Change function when discount drop down trigger*/
    $(document).on('change', '#reference_id_1', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var reference_id = $('#reference_id_1').val();

        if (reference_id > 0) {
            $("#discount_type_1").val('Fixed').trigger('change');
            $("#discount_value_1").prop("disabled", false);
        }
    });

    /*keyup function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */
    $("#discount_value_1").keyup(function () {
        keyfunction();
    });

    /*blur function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */

    $("#discount_value_1").blur(function () {
        keyfunction();
    });
    /*
    * Check Discount type and it custom than call keyfunction
    *
    */
    $(document).on('change', '#discount_type_1', function () {
        keyfunction();
    });

    /*save data for both predefined discounts and keyup trigger*/
    $("#AddPackage_1").click(function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();
        $('#PromotionDiscount').hide();
        $(this).attr("disabled", true);

        var random_id = $('#random_id_1').val();
        var unique_id = $('#unique_id_1').val();
        var service_id = $('#service_id_1').val(); //Basicailly it is bundle id
        var reference_id = $('#reference_id_1').val();
        var discount_id = $('#discount_id_1').val();
        var net_amount = $('#net_amount_1').val();
        var discount_type = $('#discount_type_1').val();
        var discount_price = $('#discount_value_1').val();
        var discount_slug = $("#slug_1").val();
        var package_total = $('#package_total_1').val();

        var is_exclusive = $('#is_exclusive').val();
        var location_id = $('#location_id_1').val();

        if (service_id && net_amount && location_id) {
            if (discount_slug == 'custom') {
                if (discount_price == '') {
                    $('#inputfieldMessage').show();
                    return false;
                }
                if (discount_type == 'Percentage') {
                    if (discount_price > 100) {
                        $('#percentageMessage').show();
                        return false;
                    }
                }
            }

            var formData = {
                'random_id': random_id,
                'unique_id': unique_id,
                'bundle_id': service_id, //Basicailly it is bundle id
                'reference_id': reference_id,
                'discount_id': discount_id,
                'net_amount': net_amount,
                'discount_type': discount_type,
                'discount_price': discount_price,
                'package_total': package_total,
                'is_exclusive': is_exclusive,
                'location_id': location_id,
                'package_bundles[]': []
            };

            $(".package_bundles").each(function () {
                formData['package_bundles[]'].push($(this).val());
            });

            $.ajax({
                type: 'get',
                url: route('admin.packages.savepackages_service'),
                data: formData,
                success: function (resposne) {

                    if (resposne.status == '1') {
                        if (reference_id > 0) {
                            $("#discount_id_1").val(discount_id).trigger('change')
                        }
                        $('#table_1').append("" +
                            "<tr id='table_1' class='HR_" + random_id + " HR_" + resposne.myarray.record.id + "'>" +
                            "<td><a href='javascript:void(0)' onClick='toggle(" + resposne.myarray.record.id + ")'>" + resposne.myarray.service_name + "</a></td>" +
                            "<td>" + resposne.myarray.service_price.toLocaleString() + "</td>" +
                            "<td>" + resposne.myarray.discount_name + "</td>" +
                            "<td>" + resposne.myarray.discount_type + "</td>" +
                            "<td>" + resposne.myarray.discount_price + "</td>" +
                            "<td>" + resposne.myarray.record.tax_exclusive_net_amount.toLocaleString() + "</td>" +
                            "<td>" + resposne.myarray.record.tax_percenatage + "</td>" +
                            "<td>" + resposne.myarray.record.tax_including_price.toLocaleString() + "</td>" +
                            "<td>" +
                            "<input type='hidden' class='package_bundles' name='package_bundles[]' value='" + resposne.myarray.record.id + "' />" +
                            "<button class='btn btn-xs btn-danger' onClick='deleteModel(" + resposne.myarray.record.id + ")'>Delete</button>" +
                            "</td>" +
                            "</tr>");

                        jQuery.each(resposne.myarray.record_detail, function (i, record_detail) {
                            if (record_detail.is_consumed == '0') {
                                var consume = 'NO';
                            } else {
                                var consume = 'YES';
                            }
                            $('#table_1').append("<tr class='inner_records_hr HR_" + resposne.myarray.record.id + " " + resposne.myarray.record.id + "'><td></td><td>" + record_detail.name + "</td><td>Amount : " + record_detail.tax_exclusive_price.toLocaleString() + "</td><td>Tax % : " + record_detail.tax_percenatage + "</td><td>Tax Amt. : " + record_detail.tax_including_price.toLocaleString() + "</td><td colspan='4'>Is Consume : " + consume + "</td></tr>");
                        });
                        toggle(resposne.myarray.record.id);

                        $("#package_total_1").val(resposne.myarray.total);

                        keyfunction_grandtotal();

                        var rows = $('#table_1 tbody tr').length;

                        if (rows >= 3) {
                            $("#location_id_1").prop("disabled", true);
                        }
                        /*we enable add button after all functionality enable*/
                        $('#AddPackage_1').attr("disabled", false);

                    } else {
                        $('#AddPackage_1').attr("disabled", false);
                        if (resposne.code == '422') {
                            $('#AlreadyExitMessage').show();
                        }
                        if (resposne.code == '423') {
                            $('#PromotionDiscount').show();
                        }
                    }
                }
            });
        } else {
            $('#inputfieldMessage').show();
            $(this).attr("disabled", false);
        }
    });
    /*End*/

    /*function for final package information save*/
    $("#AddPackageFinal_1").click(function () {

        $(this).attr("disabled", true);

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();
        $('#PromotionDiscountSave').hide();

        var random_id = $('#random_id_1').val();
        var unique_id = $('#unique_id_1').val();
        var patient_id = $('#client_id').val();
        var total = $('#package_total_1').val();
        var payment_mode_id = $('#payment_mode_id_1').val();
        var cash_amount = $('#cash_amount_1').val();
        var grand_total = $('#grand_total_1').val();
        var location_id = $('#location_id_1').val();
        var is_exclusive = $('#is_exclusive').val();
        var appointment_id = $('#appointment_id_1').val();
        var lead_source_id = $('#lead_source_id_1').val();

        var appointment_id_1 = $('#appointment_id_1').val().split(".");

        var formData = {
            'random_id': random_id,
            'unique_id': unique_id,
            'patient_id': patient_id,
            'location_id': location_id,
            'total': total,
            'payment_mode_id': payment_mode_id,
            'cash_amount': cash_amount,
            'grand_total': grand_total,
            'is_exclusive': is_exclusive,
            'appointment_id': appointment_id,
            'lead_source_id': lead_source_id,
            'package_bundles[]': []
        };

        $(".package_bundles").each(function () {
            formData['package_bundles[]'].push($(this).val());
        });
        var status = 0;
        if (cash_amount > 0) {
            var status = 1;
        }
        if (random_id && (patient_id > 0) && total && status == 1 ? payment_mode_id : true && cash_amount >= 0 && grand_total && location_id && patient_id && appointment_id_1[1] == 'D' ? lead_source_id : 1) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.savepackages'),
                data: formData,
                success: function (resposne) {
                    // console.log("from appointment plans");
                    // return "from appointment plans";
                    if (resposne.status == '1') {
                        $('#successMessage').show();
                        window.location = route('admin.appointments.index');
                    } else {
                        $('#AddPackageFinal_1').attr("disabled", false);
                        if (resposne.code == 422) {
                            $('#wrongMessage').show();
                        }
                        if (resposne.code == 423) {
                            $('#PromotionDiscountSave').show();
                        }
                    }
                }
            });
        } else {
            $('#inputfieldMessage').show();
            $(this).attr("disabled", false);
        }
    });
    /*End*/

    /*calculate grand total when user add amount after payment mode*/
    $("#cash_amount_1").keyup(function () {
        keyfunction_grandtotal();
    });
    $("#cash_amount_1").blur(function () {
        keyfunction_grandtotal();
    });
    /*End*/

    /*Define weather plans is exclusive or not*/
    $('#is_exclusive').change(function () {
        if ($(this).is(":checked")) {
            $('#is_exclusive').val('1');
        } else {
            $('#is_exclusive').val('0');
        }
    });


});

/*key function for net amount of service*/
function keyfunction() {

    $('#wrongMessage').hide();
    $('#inputfieldMessage').hide();
    $('#percentageMessage').hide();
    $('#AlreadyExitMessage').hide();
    $('#DiscountRange').hide();

    var service_id = $('#service_id_1').val();//Basicailly it is bundle id
    var discount_id = $('#discount_id_1').val();
    var discount_value = $('#discount_value_1').val();
    var discount_type = $('#discount_type_1').val();

    if (!discount_value) {
        discount_value = 0;
    }

    var div = $(this).parents();

    if (discount_type == 'Percentage') {
        if (discount_value > 100) {
            $('#percentageMessage').show();
            return false;
        } else {
            $('#percentageMessage').hide();
        }
    }
    if (service_id && discount_id && discount_type) {
        $.ajax({
            type: 'get',
            url: route('admin.packages.getdiscountgroup'),
            data: {
                'discount_id': discount_id,
            },
            success: function (resposne) {
                if (resposne.status == '1') {
                    if (resposne.group == 'custom' || resposne.group == 'special') {
                        $.ajax({
                            type: 'get',
                            url: route('admin.packages.getdiscountinfo_custom'),
                            data: {
                                'service_id': service_id, //Basicailly it is bundle id
                                'discount_id': discount_id,
                                'discount_value': discount_value,
                                'discount_type': discount_type,
                            },
                            success: function (resposne) {
                                // $("#subscription_discount").val(0);
                                if (resposne.status == '1') {
                                    $("#net_amount_1").val(resposne.net_amount);
                                    $("#net_amount_1").prop("disabled", true);
                                } else {
                                    $('#DiscountRange').show();
                                    $("#net_amount_1").val('');
                                    $("#net_amount_1").prop("disabled", true);
                                }
                            },
                        });
                    }
                    if (resposne.group == 'periodic') {
                        $.ajax({
                            type: 'get',
                            url: route('admin.packages.getdiscountinfo_periodic'),
                            data: {
                                'service_id': service_id, //Basicailly it is bundle id
                                'reference_id': $('#reference_id_1').val(),
                                'discount_id': discount_id,
                                'discount_value': discount_value,
                                'discount_type': discount_type,
                                'random_id': $('#random_id_1').val(),
                                'unique_id': $('#unique_id_1').val()
                            },
                            success: function (resposne) {
                                if (resposne.status == '1') {
                                    $("#net_amount_1").val(resposne.net_amount);
                                    $("#net_amount_1").prop("disabled", true);
                                } else {
                                    $('#DiscountRange').show();
                                    $("#net_amount_1").val('');
                                    $("#net_amount_1").prop("disabled", true);
                                }
                            },
                        });
                    }
                }
            },
        });
    }
}

/*key function for net amount of service*/
function keyfunction_grandtotal() {
    $('#wrongMessage').hide();
    $('#inputfieldMessage').hide();
    $('#percentageMessage').hide();
    $('#AlreadyExitMessage').hide();
    var cash_amount = $('#cash_amount_1').val();
    var total = $('#package_total_1').val();
    if (cash_amount && total) {
        $.ajax({
            type: 'get',
            url: route('admin.packages.getgrandtotal'),
            data: {
                'cash_amount': cash_amount,
                'total': total,
            },
            success: function (resposne) {
                if (resposne.status == '1') {
                    $("#grand_total_1").val(resposne.grand_total);
                } else {
                    $('#wrongMessage').show();
                }
            },
        });
    } else {
        $('#inputfieldMessage').show();
    }
}

/*End*/

/*Delete The record*/
function deleteModel(id) {
    $('#wrongMessage').hide();
    $('#inputfieldMessage').hide();
    $('#percentageMessage').hide();
    $('#AlreadyExitMessage').hide();
    var package_total = $('#package_total_1').val();
    $.ajax({
        type: 'post',
        url: route('admin.packages.deletepackages_service'),
        data: {
            '_token': $('input[name=_token]').val(),
            'id': id,
            'package_total': package_total
        },
        success: function (resposne) {
            if (resposne.status == '1') {

                $('.HR_' + resposne.id).remove();
                $("#package_total_1").val(resposne.total);
                keyfunction_grandtotal();

                var rows = $('#table_1 tbody tr.HR_' + $('#random_id_1').val()).length;
                if (rows <= 1) {
                    $("#location_id_1").prop("disabled", false);
                }

            } else {
                $('#wrongMessage').show();
            }
        }
    });
}

/*End*/

/*Toogle Function for display and hide package content*/
function toggle(id) {
    $("." + id).toggle();
}

/*End*/

/*active select drop down*/
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
});
/*End*/