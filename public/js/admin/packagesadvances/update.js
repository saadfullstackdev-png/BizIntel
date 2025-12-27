$(document).ready(function () {
    $(document).on('change', '#patient_id', function () {
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
                    $('#package_id')
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
    //$('#package_id').change(getpackageinfo);

    /*keyup function trigger whan we enter discount value
    * call function inside
    *  @return net amount of service
    * */
    $("#cash_amount").keyup(getpackageinfo_update);

    /*blur function trigger whan we enter discount value
    * call function inside
    * @return net amount of service
    * */

    $("#cash_amount").blur(getpackageinfo_update);

    /*function for final package advances information save*/
    $("#AddAmount").click(function () {

        $('#inputfieldMessage').hide();
        $('#exceedMessage').hide();

        var patient_id = $('#patient_id').val();
        var package_id = $('#package_id').val();
        var total_price = $('#total_price').val();
        var cash_total_amount = $('#cash_total_amount').val();
        var payment_mode_id = $('#payment_mode_id').val();
        var cash_amount = $('#cash_amount').val();
        var package_advance_id = $('#package_advance_id').val();

        if (patient_id && package_id && payment_mode_id && payment_mode_id && cash_amount) {
            $.ajax({
                type: 'get',
                url: route('admin.packagesadvances.updatepackagesadvances'),
                data: {
                    'patient_id': patient_id,
                    'package_id': package_id,
                    'total_price': total_price,
                    'cash_total_amount': cash_total_amount,
                    'payment_mode_id': payment_mode_id,
                    'cash_amount': cash_amount,
                    'package_advance_id':package_advance_id
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

function getpackageinfo_update(){

    $('#exceedMessage').hide();

    var package_id = $('#package_id').val();
    var cash_amount = $('#cash_amount').val();
    var cash_amount_update = $('#cash_amount_update').val();
    var total_price = $('#total_price').val();

    var div = $(this).parents();
    var op = " ";

    $.ajax({
        type: 'get',
        url: route('admin.packagesadvances.getpackagesinfo_update'),
        data: {
            'id': package_id,
            'cash_amount':cash_amount,
            'cash_amount_update':cash_amount_update,
            'total_price':total_price
        },
        success: function (resposne) {
            console.log(resposne.total_price);
            if (resposne.status == '1') {
                $('#total_price').val(resposne.total_price);
                $('#cash_total_amount').val(resposne.cash_amount_sum);
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
