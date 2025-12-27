$(document).ready(function () {

    $('#package_id_create').on('change', function () {

        $('#price_create').val('0');
        $('#balance_create').val('0');
        $('#cash_create').val('0');
        $('#settle_create').val('0');
        $('#outstand_create').val('0');

        var package_id_create = $('#package_id_create').val();
        var appointment_id_create = $('#appointment_id_create').val();
        var price_create = $('#price_create').val();

        if (price_create == 0) {
            $("#addinvoice").hide();
        } else {
            $("#addinvoice").show();
        }
        if (package_id_create) {
            $.ajax({
                type: 'get',
                url: route('admin.appointments.getplansinformation'),
                data: {
                    'package_id_create': package_id_create,
                    'appointment_id_create': appointment_id_create
                },
                success: function (resposne) {

                    if (resposne.status == '1') {
                        $('#table_1').find('tbody').remove();
                        jQuery.each(resposne.packagebundles, function (i, packagebundles) {

                            if (packagebundles.discount_id == null) {
                                var discountname = '-';
                            } else {
                                var discountname = packagebundles.discountname;
                            }
                            if (packagebundles.discount_type == null) {
                                var discounttype = '-';
                            } else {
                                var discounttype = packagebundles.discount_type;
                            }
                            if (packagebundles.discount_price == null) {
                                var discountprice = '0.00';
                            } else {
                                var discountprice = packagebundles.discount_price;
                            }
                            $('#table_1').append("<tr id='table_1' class='HR_" + packagebundles.id + "'><td><a href='javascript:void(0)' onClick='toggle(" + packagebundles.id + ")'>" + packagebundles.bundlename + "</a></td><td>" + parseInt(packagebundles.service_price).toLocaleString() + "</td><td>" + discountname + "</td><td>" + discounttype + "</td><td>" + discountprice + "</td><td>" + parseInt(packagebundles.tax_exclusive_net_amount).toLocaleString() + "</td><td>" + packagebundles.tax_percenatage + "</td><td>" + packagebundles.tax_including_price.toLocaleString()+ "</td></tr>");

                            jQuery.each(resposne.packageservices, function (i, packageservices) {

                                if (packageservices.package_bundle_id == packagebundles.id) {

                                    if (packageservices.is_consumed == '0') {
                                        var consume = 'NO';
                                        $('#table_1').append("<tr class='HR_" + packagebundles.id + " " + packagebundles.id + "'><td><input type='checkbox' class='invoicecheckbox' value=" + packageservices.id + "></td><td>" + packageservices.servicename + "</td><td>Amount : " + packageservices.tax_exclusive_price.toLocaleString() + "</td><td>Tax % : " + packageservices.tax_percenatage + "</td><td>Tax Amt. : " + packageservices.tax_including_price.toLocaleString() + "</td><td colspan='4'>Is Consume : " + consume + "</td></tr>");
                                    } else {
                                        var consume = 'YES';
                                        $('#table_1').append("<tr class='HR_" + packagebundles.id + " " + packagebundles.id + "'><td></td><td>" + packageservices.servicename + "</td><td>Amount : " + packageservices.tax_exclusive_price.toLocaleString() + "</td><td>Tax % : " + packageservices.tax_percenatage + "</td><td>Tax Amt. : " + packageservices.tax_including_price.toLocaleString() + "</td><td colspan='4'>Is Consume : " + consume + "</td></tr>");
                                    }
                                }
                            });
                        });
                        $('.invoicecheckbox').click(function () {
                            $(".invoicecheckbox").prop('checked', false);
                            $(this).prop('checked', true);
                            /*Here I need to set the bundle id so I can Checked on save exclusive*/
                            $('#checked_bundle_id').val($(this).val());
                            calculateInvoice(id = $(this).val());
                        });
                    }
                }
            });
        }
    });
    $('#package_id_create').change();


    /*Invoice Save and also package advances*/
    $("#savepackageinformation").click(function () {

        $(this).attr("disabled", true);

        $('#wrongMessage').hide();
        $('#successMessage').hide();
        $('#definefield').hide();
        $('#definetreatment').hide();

        var appointment_id = $('#appointment_id_create').val();
        var appointment_id_consultancy = $('#appointment_link_cons').val();
        var package_id = $('#package_id_create').val();
        var amount_create = $('#amount_create').val();
        var tax_create = $('#tax_create').val();
        var price = $('#price_create').val();
        var balance = $('#balance_create').val();
        var cash = $('#cash_create').val();
        var settle = $('#settle_create').val();
        var outstand = $('#outstand_create').val();
        var package_service_id = $('#package_service_id').val();
        var package_mode_id = $('#payment_mode_id').val();
        var checked_treatment = $('#checked_treatment').val();
        var created_at = $('#created_at').val();
        var tax_treatment_type_id = $('#tax_treatment_type_id').val();

        var status_checked_treatment = true;

        if(checked_treatment == 0){
            var exclusive_or_bundle = $('#checked_bundle_id').val();
            if(exclusive_or_bundle == 0){
                //if treatment belongs to plan but not select to I set that varibale
                 var status_checked_treatment = false;
            }
        } else {
            var exclusive_or_bundle = $('#is_exclusive').val();
        }

        var status = true;

        if (cash > 0) {
            if(package_mode_id=='') {
                status = false;
            }
        }
        if(status_checked_treatment){
            if(status){
                if (appointment_id && price && balance && cash && settle && outstand) {
                    $.ajax({
                        type: 'get',
                        url: route('admin.appointments.saveinvoice'),
                        data: {
                            'appointment_id': appointment_id,
                            'package_id': package_id,
                            'amount_create':amount_create,
                            'tax_create':tax_create,
                            'price': price,
                            'balance': balance,
                            'cash': cash,
                            'settle': settle,
                            'outstand': outstand,
                            'package_service_id': package_service_id,
                            'package_mode_id': package_mode_id,
                            'checked_treatment':checked_treatment,
                            'exclusive_or_bundle':exclusive_or_bundle,
                            'created_at':created_at,
                            'appointment_id_consultancy':appointment_id_consultancy,
                            'tax_treatment_type_id':tax_treatment_type_id
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
            } else {
                $('#definefield').show();
            }
        } else {
            $('#definetreatment').show();
            $(this).attr("disabled", false);
        }

    });

    /*keyup function trigger whan we enter cash amount*/
    $("#cash_create").keyup(function () {
        keyfunction();
    });

    /*blur function trigger whan we enter cash value*/
    $("#cash_create").blur(function () {
        keyfunction();
    });

    /*Trigger function when popup load*/
    $("#cash_create").blur();

    /*Make functional exclusive checked box*/
    $("#is_exclusive").change(function () {
        if ($(this).is(":checked")) {
            $('#is_exclusive').val('1');
        }
        else {
            $('#is_exclusive').val('0');
        }
        var price_orignal = $('#orignal_price_h').val();
        var location_id = $('#location_id_tax').val();
        var is_exclusive =  $('#is_exclusive').val();
        var tax_treatment_type_id =  $('#tax_treatment_type_id').val();
        if (price_orignal) {
            $.ajax({
                type: 'get',
                url: route('admin.appointments.getcalculatedPriceExclusicecheck'),
                data: {
                    'price_orignal': price_orignal,
                    'location_id': location_id,
                    'is_exclusive': is_exclusive,
                    'tax_treatment_type_id':tax_treatment_type_id,
                },
                success: function (resposne) {
                    if (resposne.status) {
                        $('#amount_create').val(resposne.amount_create);
                        $('#tax_create').val(resposne.tax_create);
                        $('#price_create').val(resposne.price);
                        $('#cash_create').val('0');
                        $("#outstand_create").val(resposne.outstdanding);
                        $("#addinvoice").hide();
                    }
                },
            });
        }

    });
});

/*keyup function for $net_amount*/
function keyfunction() {
    var price_create = $('#price_create').val();
    var balance_create = $('#balance_create').val();
    var cash_create = $('#cash_create').val();
    var settleamount_for_zero = $('#settleamount_for_zero').val();
    var outstanding_for_zero = $('#outstanding_for_zero').val();

    if (cash_create == 0 || cash_create == '') {
        $('#paymentmode').hide();
    } else {
        $('#paymentmode').show();
    }

    if (!cash_create) {
        $("#settle_create").val(settleamount_for_zero);
        $("#outstand_create").val(outstanding_for_zero);
    }
    var div = $(this).parents();
    if (price_create && balance_create && cash_create) {
        $.ajax({
            type: 'get',
            url: route('admin.appointments.getinvoicecalculation'),
            data: {
                'price_create': price_create,
                'balance_create': balance_create,
                'cash_create': cash_create,
                'settleamount_for_zero': settleamount_for_zero,
                'outstanding_for_zero': outstanding_for_zero
            },
            success: function (resposne) {
                if (resposne.status == '1') {

                    $("#settle_create").val(resposne.settleamount);
                    $("#outstand_create").val(resposne.outstdanding);
                    console.log("Response of outstanding  " + resposne.outstdanding);
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

/*Calcuate invoice data and return data according to price*/
function calculateInvoice(id) {
    $('#wrongMessage').hide();
    $('#definetreatment').hide();

    var appointment_id_create = $('#appointment_id_create').val();
    var package_id_create = $('#package_id_create').val();

    $.ajax({
        type: 'get',
        url: route('admin.appointments.getpackageprice'),
        data: {
            'package_service_id': id,
            'appointment_id_create': appointment_id_create,
            'package_id_create': package_id_create
        },
        success: function (resposne) {
            if (resposne.status == '1') {

                $('#amount_create').val(resposne.amount);
                $('#tax_create').val(resposne.tax_price);
                $('#price_create').val(resposne.serviceprice);
                $('#balance_create').val(resposne.balance);
                $('#settle_create').val(resposne.settleamount);
                $('#outstand_create').val(resposne.outstanding);
                $('#settleamount_for_zero').val(resposne.settleamount);
                $('#outstanding_for_zero').val(resposne.outstanding);
                $('#package_service_id').val(id);

                if (resposne.outstanding == '0') {
                    $("#addinvoice").show();
                } else {
                    $("#addinvoice").hide();
                }

            } else {
                $('#wrongMessage').show();
            }
        },
    });
}

/*Toogle Function for display and hide package content*/
function toggle(id) {
    $("." + id).toggle();
}

$(document).ready(function () {
    $('.select2').select2({width: '100%'});

    $('.date_to_invoice').datepicker({
        format: 'yyyy-mm-dd',
    }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    })
});