$(document).ready(function () {

    /*Change when appointment id change*/
    $(document).on('change', '#appointment_id', function () {

        var appointment_id = $('#appointment_id').val().split(".");

        if (appointment_id[1] == 'D') {
            $("#lead_source_id").prop("disabled", false);
        } else {
            $("#lead_source_id").val('').trigger('change');
            $("#lead_source_id").prop("disabled", true);
        }
    });

    /*Change when service drop down trigger*/
    $(document).on('change', '#service_id', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var service_id = $('#service_id').val();
        var patient_id = $('#parent_id').val();
        var location_id = $('#location_id').val();

        var div = $(this).parents();
        var op = " ";

        $("#discount_id").val('0').trigger('change')

        if (service_id && patient_id) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.getserviceinfo'),
                data: {
                    'bundle_id': service_id,
                    'location_id': location_id,
                    'patient_id': patient_id
                },
                success: function (resposne) {
                    // $("#subscription_discount").val(resposne.subscription_discount);
                    if (resposne.status == '1') {
                        op += '<option value="0" selected>Select Discount</option>';
                        $.each(resposne.discounts, function (index, obj) {
                            op += '<option value="' + obj.id + '">' + obj.name + '</option>';
                        });
                        div.find('.discount_id').html("");
                        div.find('.discount_id').append(op);

                        $("#net_amount").val(resposne.net_amount);
                        $("#net_amount").prop("disabled", true);

                    } else {

                        op += '<option value="0" selected>Select Discount</option>';
                        div.find('.discount_id').html("");
                        div.find('.discount_id').append(op);

                        $("#net_amount").val(resposne.net_amount);
                        $("#net_amount").prop("disabled", true);
                    }
                },
            });
        }
    });

    /*Change function when discount drop down trigger*/
    $(document).on('change', '#discount_id', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var service_id = $('#service_id').val(); //Basicailly it is bundle id
        var discount_id = $(this).val();
        var random_id = $('#random_id').val();
        var unique_id = $('#unique_id').val();

        var div = $(this).parents();
        var op = " ";

        if (service_id == null && discount_id == null) {
            $("#reference_id").prop("disabled", true);
            $("#reference_id").val('0').trigger('change');
            $("#discount_type").prop("disabled", false);
            $("#discount_type").val('').trigger('change');
            $("#discount_value").prop("disabled", false);
            $("#discount_value").val('');
            $("#net_amount").prop("disabled", false);
            $("#net_amount").val('');
            $("#slug").val('not_custom');

            op += '<option value="0" selected>Select Discount</option>';
            div.find('.discount_id').html("");
            div.find('.discount_id').append(op);

        } else if (discount_id == null && service_id != null) {
            $("#reference_id").prop("disabled", true);
            $("#reference_id").val('0').trigger('change');
            $("#discount_type").prop("disabled", true);
            $("#discount_type").val('').trigger('change');
            $("#discount_value").prop("disabled", true);
            $("#discount_value").val('');
            $("#slug").val('not_custom');

        } else if (service_id == null && discount_id == '0') {
            $("#reference_id").prop("disabled", true);
            $("#reference_id").val('0').trigger('change');
            $("#discount_type").prop("disabled", false);
            $("#discount_type").val('').trigger('change');
            $("#discount_value").prop("disabled", false);
            $("#discount_value").val('');
            $("#net_amount").prop("disabled", false);
            $("#net_amount").val('');

            $("#slug").val('not_custom');

            op += '<option value="0" selected>Select Discount</option>';
            div.find('.discount_id').html("");
            div.find('.discount_id').append(op);

        } else if (service_id && discount_id == '0') {
            $("#slug").val('not_custom');
            $.ajax({
                type: 'get',
                url: route('admin.packages.getserviceinfo_discount_zero'),
                data: {
                    'bundle_id': service_id, //Basicailly it is bundle id
                },
                success: function (resposne) {
                    if (resposne.status == '1') {

                        $("#reference_id").prop("disabled", true);
                        $("#reference_id").val('0').trigger('change');
                        $("#discount_type").prop("disabled", true);
                        $("#discount_type").val('').trigger('change');
                        $("#discount_value").prop("disabled", true);
                        $("#discount_value").val('');
                        $("#net_amount").val(resposne.net_amount);
                        $("#net_amount").prop("disabled", true);
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
                                $("#reference_id").prop("disabled", true);
                                $("#reference_id").val('0').trigger('change');
                                $("#discount_type").val(resposne.discount_type).change();
                                $("#discount_type").prop("disabled", true);
                                $("#discount_value").val(resposne.discount_price);
                                $("#discount_value").prop("disabled", true);
                                $("#net_amount").val(resposne.net_amount);
                                $("#net_amount").prop("disabled", true);
                                $("#slug").val('not_custom');

                            } else if (resposne.slug == 'periodic') {
                                console.log(resposne)
                                ref += '<option value="0" selected>Select Reference</option>';
                                $.each(resposne.references, function (index, obj) {
                                    ref += '<option value="' + obj.id + '">' + obj.name + '</option>';
                                });
                                div.find('.reference_id').html("");
                                div.find('.reference_id').append(ref);

                                $("#reference_id").prop("disabled", false);

                                $("#discount_type").prop("disabled", true);
                                $("#discount_type").val('').trigger('change');

                                $("#discount_value").prop("disabled", true);
                                $("#discount_value").val('');

                                $("#net_amount").val(resposne.net_amount);
                                $("#net_amount").prop("disabled", true);

                            } else {
                                $("#reference_id").prop("disabled", true);
                                $("#reference_id").val('0').trigger('change');
                                $("#discount_type").prop("disabled", false);
                                $("#discount_type").val('').trigger('change');
                                $("#discount_value").prop("disabled", false);
                                $("#discount_value").val('');
                                $("#net_amount").prop("disabled", true);
                                $("#net_amount").val('');
                                $("#slug").val('custom');
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
    $(document).on('change', '#reference_id', function () {

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();

        var reference_id = $('#reference_id').val();

        if (reference_id > 0) {
            $("#discount_type").val('Fixed').trigger('change');
            $("#discount_value").prop("disabled", false);
        }
    });

    /*keyup function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */
    $("#discount_value").keyup(function () {
        keyfunction();
    });

    /*blur function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */
    $("#discount_value").blur(function () {
        keyfunction();
    });

    /*
    * Check Discount type and it custom than call keyfunction
    */
    $(document).on('change', '#discount_type', function () {
        keyfunction();
    });

    /*save data for both predefined discounts and keyup trigger*/
    $("#AddPackage").click(function () {

        $('#inputfieldMessage').hide();
        $('#PromotionDiscount').hide();
        $('#wrongMessage').hide();

        $(this).attr("disabled", true);

        var random_id = $('#random_id').val();
        var unique_id = $('#unique_id').val();
        var service_id = $('#service_id').val();
        var reference_id = $('#reference_id').val();
        var discount_id = $('#discount_id').val();
        var net_amount = $('#net_amount').val();
        var discount_type = $('#discount_type').val();
        var discount_price = $('#discount_value').val();
        var discount_slug = $("#slug_1").val();
        var package_total = $('#package_total').val();

        var is_exclusive = $('#is_exclusive').val();
        var location_id = $('#location_id').val();

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
                'bundle_id': service_id,
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
                            $("#discount_id").val(discount_id).trigger('change')
                        }
                        $('#table').append("" +
                            "<tr id='table' class='HR_" + resposne.myarray.record.id + "'>" +
                            "<td><a href='javascript:void(0)' onClick='toggle(" + resposne.myarray.record.id + ")'>" + resposne.myarray.service_name + "</a></td>" +
                            "<td>" + resposne.myarray.service_price.toLocaleString() + "</td><td>" + resposne.myarray.discount_name + "</td>" +
                            "<td>" + resposne.myarray.discount_type + "</td><td>" + resposne.myarray.discount_price + "</td>" +
                            "<td>" + resposne.myarray.record.tax_exclusive_net_amount.toLocaleString() + "</td>" +
                            "<td>" + resposne.myarray.record.tax_percenatage + "</td>" +
                            "<td>" + resposne.myarray.record.tax_including_price.toLocaleString() + "</td>" +
                            "<td>" +
                            "<input type='hidden' class='package_bundles' name='package_bundles[]' value='" + resposne.myarray.record.id + "' />" +
                            "<button class='btn btn-xs btn-danger' onClick='deleteModel(" + resposne.myarray.record.id + ")'>Delete</button>" +
                            "</td>" +
                            "</tr>"
                        );
                        jQuery.each(resposne.myarray.record_detail, function (i, record_detail) {
                            if (record_detail.is_consumed == '0') {
                                var consume = 'NO';
                            } else {
                                var consume = 'YES';
                            }
                            $('#table').append("<tr style ='display:none;' class='HR_" + resposne.myarray.record.id + " " + resposne.myarray.record.id + "'><td></td><td>" + record_detail.name + "</td><td>Amount : " + record_detail.tax_exclusive_price.toLocaleString() + "</td><td>Tax % : " + record_detail.tax_percenatage + "</td><td>Tax Amt. : " + record_detail.tax_including_price.toLocaleString() + "</td><td colspan='4'>Is Consumed : " + consume + "</td></tr>");
                        });
                        toggle(resposne.myarray.record.id);

                        $("#package_total").val(resposne.myarray.total);

                        keyfunction_grandtotal();

                        /*we enable add button after all functionality performed*/
                        $('#AddPackage').attr("disabled", false);
                    } else {
                        $('#AddPackage').attr("disabled", false);
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

    /*function for final package information save*/
    $("#AddPackageFinal").click(function () {

        $(this).attr("disabled", true);

        $('#wrongMessage').hide();
        $('#inputfieldMessage').hide();
        $('#percentageMessage').hide();
        $('#AlreadyExitMessage').hide();
        $('#PromotionDiscountSave').hide();

        var random_id = $('#random_id').val();
        var unique_id = $('#unique_id').val();
        var patient_id = $('#parent_id').val();
        var total = $('#package_total').val();
        var payment_mode_id = $('#payment_mode_id').val();
        var cash_amount = $('#cash_amount').val();
        var grand_total = $('#grand_total').val();
        var location_id = $('#location_id').val();
        var appointment_id = $('#appointment_id').val();
        var lead_source_id = $('#lead_source_id').val();

        var appointment_id_1 = $('#appointment_id').val().split(".");

        var formData = {
            'random_id': random_id,
            'unique_id': unique_id,
            'patient_id': patient_id,
            'total': total,
            'payment_mode_id': payment_mode_id,
            'cash_amount': cash_amount,
            'grand_total': grand_total,
            'location_id': location_id,
            'appointment_id': appointment_id,
            'lead_source_id': lead_source_id,
            'package_bundles[]': []
        };

        $(".package_bundles").each(function () {
            formData['package_bundles[]'].push($(this).val());
        });
        var status = 0;
        if (cash_amount > 0) {
            status = 1;
        }
        if (random_id && total && status == 1 ? payment_mode_id : true && cash_amount >= 0 && grand_total && location_id && appointment_id_1[1] == 'D' ? lead_source_id : 1) {
            $.ajax({
                type: 'get',
                url: route('admin.packages.updatepackages'),
                data: formData,
                success: function (resposne) {

                    if (resposne.status == '1') {
                        $('#successMessage').show();
                        window.location = route('admin.packages.index');
                    } else {
                        $('#AddPackageFinal').attr("disabled", false);
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

    /*calculate grand total when user add amount after payment mode*/
    $("#cash_amount").keyup(function () {
        keyfunction_grandtotal();
    });

    $("#cash_amount").blur(function () {
        keyfunction_grandtotal();
    });

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

    var service_id = $('#service_id').val();
    var discount_id = $('#discount_id').val();
    var discount_value = $('#discount_value').val();
    var discount_type = $('#discount_type').val();

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
                                    $("#net_amount").val(resposne.net_amount);
                                    $("#net_amount").prop("disabled", true);
                                } else {
                                    $('#DiscountRange').show();
                                    $("#net_amount").val('');
                                    $("#net_amount").prop("disabled", true);
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
                                'reference_id': $('#reference_id').val(),
                                'discount_id': discount_id,
                                'discount_value': discount_value,
                                'discount_type': discount_type,
                                'random_id': $('#random_id').val(),
                                'unique_id': $('#unique_id').val()
                            },
                            success: function (resposne) {
                                if (resposne.status == '1') {
                                    $("#net_amount").val(resposne.net_amount);
                                    $("#net_amount").prop("disabled", true);
                                } else {
                                    $('#DiscountRange').show();
                                    $("#net_amount").val('');
                                    $("#net_amount").prop("disabled", true);
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
    var cash_amount = $('#cash_amount').val();
    var total = $('#package_total').val();
    var random_id = $('#random_id').val();
    if (cash_amount && total) {
        $.ajax({
            type: 'get',
            url: route('admin.packages.getgrandtotal_update'),
            data: {
                'cash_amount': cash_amount,
                'total': total,
                'random_id': random_id
            },
            success: function (resposne) {
                if (resposne.status == '1') {
                    $("#grand_total").val(resposne.grand_total);
                } else {
                    $('#wrongMessage').show();
                }
            },
        });
    } else {
        $('#inputfieldMessage').show();
    }
}

/*Delete The record*/
function deleteModel(id) {
    $('#wrongMessage').hide();
    $('#inputfieldMessage').hide();
    $('#percentageMessage').hide();
    $('#AlreadyExitMessage').hide();
    var package_total = $('#package_total').val();
    var update_status = 1;
    $.ajax({
        type: 'post',
        url: route('admin.packages.deletepackages_service'),
        data: {
            '_token': $('input[name=_token]').val(),
            'id': id,
            'package_total': package_total,
            'update_status': update_status
        },
        success: function (resposne) {
            if (resposne.status == '1') {
                $('.HR_' + resposne.id).remove();
                $("#package_total").val(resposne.total);
                keyfunction_grandtotal();
            } else {
                $('#consumeservice').show();
            }
        }
    });
}

/*Delete The record*/
function deleteCashModel(package_advance_id, package_id) {

    $('#wrongMessage').hide();
    $('#inputfieldMessage').hide();
    $('#percentageMessage').hide();
    $('#AlreadyExitMessage').hide();
    var cash_receveive_remain = $('#grand_total').val();
    $.ajax({
        type: 'post',
        url: route('admin.packages.delete_cash'),
        data: {
            '_token': $('input[name=_token]').val(),
            'package_advance_id': package_advance_id,
            'cash_receveive_remain': cash_receveive_remain
        },
        success: function (resposne) {
            if (resposne.status == '1') {
                $('.fianance_edit_' + resposne.id).remove();
                $("#grand_total").val(resposne.cash_receveive_remain);
            } else {
                $('#consumeprice').show();
            }

        }
    });
}

/*Toogle Function for display and hide package content*/
function toggle(id) {
    $("." + id).toggle();
}

/*active select drop down*/

/*End*/
