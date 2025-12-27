$(document).ready(function () {
    $("#is_exclusive_consultancy").change(function () {
        if ($(this).is(":checked")) {
            $('#is_exclusive_consultancy').val('1');
        }
        else {
            $('#is_exclusive_consultancy').val('0');
        }
        var discount_id = $('#discount_id').val();

        if (discount_id) {
            $.ajax({
                type: 'get',
                url: route('admin.appointments.checkedcustom'),
                data: {
                    'discount_id': discount_id,
                },
                success: function (response) {
                    if (response.status) {
                        $('#discount_value').val('0');
                        $('#discount_id').val('0').change();
                    } else {
                        $('#discount_id').val('0').change();
                    }
                },
            });
        }
    });
    $('#discount_id').on('change', function () {

        var is_exclusive_consultancy = $('#is_exclusive_consultancy').val();
        var location_id = $('#id_location').val();
        var appointment_id = $('#appointment_id').val();
        var discount_id = $('#discount_id').val();
        var price_for_calculation = $('#price_for_calculation').val();
        var tax_treatment_type_id = $('#tax_treatment_type_id').val();

        /*Set value cash 0 when discount change*/
        $('#cash').val('0');
        /*End*/

        $.ajax({
            type: 'get',
            url: route('admin.appointments.getconsultancycalculation'),
            data: {
                'is_exclusive_consultancy': is_exclusive_consultancy,
                'location_id': location_id,
                'appointment_id': appointment_id,
                'discount_id': discount_id,
                'price_for_calculation': price_for_calculation,
                'tax_treatment_type_id':tax_treatment_type_id,
            },
            success: function (response) {
                if (response.status) {
                    $("#discount_type").val(response.discount_type).change();
                    $("#discount_type").prop("disabled", true);
                    $("#discount_value").val(response.discount_price);
                    $("#discount_value").prop("disabled", true);
                    $("#amount").val(response.price);
                    $("#tax").val(response.tax);
                    $("#tax_amt").val(response.tax_amt);
                    $('#settle').val(response.settleamount);
                    $('#outstand').val(response.outstanding);

                    $('#settleamount_cash').val(response.settleamount);
                    $('#outstanding_cash').val(response.outstanding);

                } else {
                    if (response.discount_ava_check == 'true') {
                        $("#discount_type").val('0').change();
                        $("#discount_type").prop("disabled", false);
                        $("#discount_value").val('0');
                        $("#discount_value").prop("disabled", false);
                        $("#amount").val(response.price);
                        $("#tax").val(response.tax);
                        $("#tax_amt").val(response.tax_amt);
                        $('#settle').val(response.settleamount);
                        $('#outstand').val(response.outstanding);

                        $('#settleamount_cash').val(response.settleamount);
                        $('#outstanding_cash').val(response.outstanding);

                    } else {
                        $("#discount_type").val('0').change();
                        $("#discount_type").prop("disabled", true);
                        $("#discount_value").val('0');
                        $("#discount_value").prop("disabled", true);
                        $("#amount").val(response.price);
                        $("#tax").val(response.tax);
                        $("#tax_amt").val(response.tax_amt);
                        $('#settle').val(response.settleamount);
                        $('#outstand').val(response.outstanding);

                        $('#settleamount_cash').val(response.settleamount);
                        $('#outstanding_cash').val(response.outstanding);
                    }
                }
            }
        });
    });

    $("#discount_value").keyup(function () {
        keyfunction_custom();
    });
    $("#discount_value").blur(function () {
        keyfunction_custom();
    });
    $(document).on('change', '#discount_type', function () {
        keyfunction_custom();
    });
    $("#cash").keyup(function () {
        keyfunction_cash();
    });
    $("#cash").blur(function () {
        keyfunction_cash();
    });

    /*Invoice Save and also package advances*/
    $("#savepackageinformation").click(function () {

        $(this).attr("disabled", true);

        $('#wrongMessage').hide();
        $('#successMessage').hide();
        $('#definefield').hide();

        var status = true;

        var appointment_id = $('#appointment_id').val();
        var amount_create = $('#amount').val();
        var tax_create = $('#tax').val();
        var price = $('#tax_amt').val();
        var balance = $('#balance').val();
        var cash = $('#cash').val();
        var settle = $('#settle').val();
        var outstand = $('#outstand').val();
        var payment_mode_id = $('#payment_mode_id').val();
        var is_exclusive = $('#is_exclusive_consultancy').val();
        var discount_id = $('#discount_id').val();
        var discount_type = $('#discount_type').val();
        var discount_value = $('#discount_value').val();
        var created_at = $('#created_at').val();
        var tax_treatment_type_id = $('#tax_treatment_type_id').val();

        if (outstand == cash) {
            $('#definefield').hide();
            status = true;
        } else {
            if (payment_mode_id == 0) {
                $('#definefield').show();
                status = false;
            } else {
                $('#definefield').hide();
                status = true;
            }
        }

        if (status) {
            $.ajax({
                type: 'get',
                url: route('admin.appointments.saveconsultancyinvoice'),
                data: {
                    'appointment_id': appointment_id,
                    'amount_create': amount_create,
                    'tax_create': tax_create,
                    'price': price,
                    'balance': balance,
                    'cash': cash,
                    'settle': settle,
                    'outstand': outstand,
                    'payment_mode_id': payment_mode_id,
                    'is_exclusive': is_exclusive,
                    'discount_id': discount_id,
                    'discount_type': discount_type,
                    'discount_value': discount_value,
                    'created_at':created_at,
                    'tax_treatment_type_id':tax_treatment_type_id,
                },
                success: function (resposne) {
                    if (resposne.status == '1') {
                        $('#successMessage').show();
                        location.reload();
                    } else {
                        $('#wrongMessage').show();
                    }
                }
            });
        }
    });
});

/*key function for net amount of service*/
function keyfunction_custom() {
    $('#percentageMessage').hide();
    var is_exclusive_consultancy = $('#is_exclusive_consultancy').val();
    var price = $('#price_for_calculation').val();
    var discount_id = $('#discount_id').val();
    var discount_type = $('#discount_type').val();
    var discount_value = $('#discount_value').val();
    var location_id = $('#id_location').val();
    var tax_treatment_type_id = $('#tax_treatment_type_id').val();

    var div = $(this).parents();
    if (discount_type == 'Percentage') {
        if (discount_value > 100) {
            $('#percentageMessage').show();
            return false;
        } else {
            $('#percentageMessage').hide();
        }
    }
    $.ajax({
        type: 'get',
        url: route('admin.appointments.checkedcustom'),
        data: {
            'discount_id': discount_id,
        },
        success: function (response) {
            if (response.status) {
                if (price && discount_id != 0 && discount_value && discount_type) {
                    $.ajax({
                        type: 'get',
                        url: route('admin.appointments.getcustomcalculation'),
                        data: {
                            'price': price, //Basicailly it is bundle id
                            'discount_id': discount_id,
                            'discount_value': discount_value,
                            'discount_type': discount_type,
                            'location_id': location_id,
                            'is_exclusive_consultancy': is_exclusive_consultancy,
                            'tax_treatment_type_id':tax_treatment_type_id
                        },
                        success: function (response) {
                            if (response.status) {
                                $("#amount").val(response.price);
                                $("#tax").val(response.tax);
                                $("#tax_amt").val(response.tax_amt);
                                $('#settle').val(response.settleamount);
                                $('#outstand').val(response.outstanding);

                                $('#settleamount_cash').val(response.settleamount);
                                $('#outstanding_cash').val(response.outstanding);

                                if (response.outstanding == '0') {
                                    $("#addinvoice").show();
                                } else {
                                    $("#addinvoice").hide();
                                }

                            } else {
                                $('#percentageMessage').show();
                                $("#amount").val('');
                            }
                        },
                    });
                }
            }
        },
    });
}

/*End*/

/*function to check cash is equal to amt amount or not*/
function keyfunction_cash() {

    var price = $('#tax_amt').val();
    /*tax amt. amount*/
    var balance = $('#balance').val();
    var cash = $('#cash').val();
    var settleamount = $('#settle').val();
    var outstanding = $('#outstand').val();

    if (cash == 0 || cash == '') {
        $('#paymentmode').hide();
    } else {
        $('#paymentmode').show();
    }

    if (!cash && cash == 0) {
        var settle_cash = $("#settleamount_cash").val();
        var outstand_cash = $("#outstanding_cash").val();
        $("#settle").val(settle_cash);
        $("#outstand").val(outstand_cash);
    }
    var div = $(this).parents();
    if (price && balance && cash) {
        $.ajax({
            type: 'get',
            url: route('admin.appointments.getfinalcalculation'),
            data: {
                'price': price,
                'balance': balance,
                'cash': cash,
                'settleamount': settleamount,
                'outstanding': outstanding
            },
            success: function (resposne) {
                if (resposne.status == '1') {
                    $("#settle").val(resposne.settleamount);
                    $("#outstand").val(resposne.outstdanding);
                    if (resposne.outstdanding == '0') {
                        $("#addinvoice").show();
                    } else {
                        $("#addinvoice").hide();
                    }
                }
            },
        });
    }
}

$(document).ready(function () {
    $('.select2').select2({width: '100%'});

    $('.date_to_invoice').datepicker({
        format: 'yyyy-mm-dd',
    }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    })

});

