$(document).ready(function () {

    $('#package_id_create').on('change', function () {
        console.log("Hello from invoice");
        var package_id_create = $('#package_id_create').val();
        var appointment_id_create = $('#appointment_id_create').val();
        $.ajax({
            type: 'get',
            url: route('admin.appointments.getpackageprice'),
            data: {
                'package_id_create': package_id_create,
                'appointment_id_create':appointment_id_create
            },
            success: function (resposne) {

                if (resposne.status == '1') {

                    $('#table_1').find('tbody').remove();

                    $('#table_1').append("<tr id='HR_'><td>" + resposne.myarray.packageinfo.name + "</td><td>" + resposne.myarray.packageinfo.price.toLocaleString()+ "</td><td>" + resposne.myarray.packageinfo.discount_name + "</td><td>" + resposne.myarray.packageinfo.discount_type + "</td><td>" + resposne.myarray.packageinfo.discount_price + "</td><td>" + parseInt(resposne.myarray.packageinfo.net_amount).toLocaleString() + "</td></tr>");

                    $('#price_create').val(resposne.serviceprice);
                    $('#settle_create').val(resposne.settleamount);
                    $('#outstand_create').val(resposne.outstanding);
                    $('#settleamount_for_zero').val(resposne.settleamount);
                    $('#outstanding_for_zero').val(resposne.outstanding);

                    if(resposne.outstanding == '0'){
                        $("#addinvoice").show();
                    } else {
                        $("#addinvoice").hide();
                    }

                } else {
                  }
            }
        });
    });
    $("#package_id_create").change();
    /*Invoice Save and also package advances*/
    $("#savepackageinformation").click(function () {

        var appointment_id = $('#appointment_id_create').val();
        var package_id = $('#package_id_create').val();
        var price = $('#price_create').val();
        var balance = $('#balance_create').val();
        var cash = $('#cash_create').val();
        var settle = $('#settle_create').val();
        var outstand = $('#outstand_create').val();

        if (appointment_id && price && balance && cash && settle && outstand) {
            $.ajax({
                type: 'get',
                url: route('admin.appointments.saveinvoice'),
                data: {
                    'appointment_id': appointment_id,
                    'package_id': package_id,
                    'price': price,
                    'balance': balance,
                    'cash': cash,
                    'settle': settle,
                    'outstand': outstand,
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
    /*End*/

    /*keyup function trigger whan we enter cash amount
    * */
    $("#cash_create").keyup(function () {
        keyfunction();
    });

    /*blur function trigger whan we enter cash value
    * */
    $("#cash_create").blur(function () {
        keyfunction();
    });

    /*Trigger function when popup load*/
    $("#cash_create").blur();

});

/*keyup function for $net_amount*/
function keyfunction() {

    var price_create = $('#price_create').val();
    var balance_create = $('#balance_create').val();
    var cash_create = $('#cash_create').val();
    var settleamount_for_zero = $('#settleamount_for_zero').val();
    var outstanding_for_zero = $('#outstanding_for_zero').val();

    if(!cash_create){

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
                'outstanding_for_zero':outstanding_for_zero
            },
            success: function (resposne) {
                if (resposne.status == '1') {
                    $("#settle_create").val(resposne.settleamount);
                    $("#outstand_create").val(resposne.outstdanding);
                    if(resposne.outstdanding == '0'){
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
    $('.select2').select2({ width: '100%' });
});