$(document).ready(function () {
    $(document).on('change', '#patient_id_1', function () {
        $('#wrongMessage').hide();
        var patient_id = $(this).val();
        var div = $(this).parents();
        var op = " ";
        $.ajax({
            type: 'get',
            url: route('admin.packagesadvances.getpackages'),
            data: {'id': patient_id},
            success: function (resposne) {
                console.log(resposne.myarray.packageinfo);
                if (resposne.status == '1') {
                    var dropdowndata = '<option value="" selected="selected">Select Package</option>';
                    for (var i = 0; i < resposne.myarray.packageinfo.length; i++) {
                        dropdowndata += '<option value="' + resposne.myarray.packageinfo[i].id + '">' + resposne.myarray.packageinfo[i].name + '</option>';
                    }
                    $('#package_id_1')
                        .find('option')
                        .remove()
                        .end()
                        .val('Select Package')
                        .append(dropdowndata);
                } else {
                    $('#wrongMessage').show();
                }
            },
        });
    });
    /*Get the information of packages total and cash amount */
    $('#package_id_1').change(getpackageinfo);

    /*keyup function trigger whan we enter discount value
    * call function inside
    *  @return net amount of service
    * */
    $("#cash_amount_1").keyup(getpackageinfo);

    /*blur function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */

    $("#cash_amount_1").blur(getpackageinfo);

    /*function for final package advances information save*/
    $("#AddAmount_1").click(function () {

        $('#inputfieldMessage').hide();
        $('#exceedMessage').hide();

        var patient_id = $('#patient_id_1').val();
        var package_id = $('#package_id_1').val();
        var total_price = $('#total_price_1').val();
        var cash_total_amount = $('#cash_total_amount_1').val();
        var payment_mode_id = $('#payment_mode_id_1').val();
        var cash_amount = $('#cash_amount_1').val();

        if (patient_id && package_id && payment_mode_id && payment_mode_id && cash_amount) {
            $.ajax({
                type: 'get',
                url: route('admin.packagesadvances.savepackagesadvances'),
                data: {
                    'patient_id': patient_id,
                    'package_id': package_id,
                    'total_price': total_price,
                    'cash_total_amount': cash_total_amount,
                    'payment_mode_id': payment_mode_id,
                    'cash_amount': cash_amount,
                },
                success: function (resposne) {

                    if (resposne.status == '1') {
                        $('#successMessage').show();
                        location.reload();
                    } else {
                        $('#exceedMessage').show();
                    }
                }
            });
        } else {
            $('#inputfieldMessage').show();
        }
    });
    /*End*/


});

function getpackageinfo(){

    $('#exceedMessage').hide();

    var package_id = $('#package_id_1').val();
    var cash_amount = $('#cash_amount_1').val();
    var div = $(this).parents();
    var op = " ";

    $.ajax({
        type: 'get',
        url: route('admin.packagesadvances.getpackagesinfo'),
        data: {
            'id': package_id,
            'cash_amount':cash_amount
        },
        success: function (resposne) {
            console.log(resposne.total_price);
            if (resposne.status == '1') {
                $('#total_price_1').val(resposne.total_price);
                $('#cash_total_amount_1').val(resposne.cash_amount_sum);
            } else {
                $('#exceedMessage').show();
            }
        },
    });
}

/*active select drop down*/
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
});
/*End*/