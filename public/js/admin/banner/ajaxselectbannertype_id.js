$(document).on('change', '#banner_type', function(){
    var selectedValue = $(this).val();
    var urlBanner;
    // alert('Selected Value: ' + selectedValue);

    if (selectedValue === 'services') {
        urlBanner="banner/get_banner_services";
    } else if (selectedValue === 'packages') {
        urlBanner="banner/get_banner_bundles";
    } else {

    }
    $('#banner_value_div').empty();
    var select = '';
    if(selectedValue == 'services' || selectedValue == 'packages'){
        $.ajax({
            url: urlBanner,
            type: 'GET',
            dataType: 'json',

            success: function(response){
                console.log(response);
                // return false;
                // Handle success response


                if (selectedValue === 'services') {
                    select += "<select name='banner_value' id='banner_value' class='form-control select2' required='required'> <option value=''>Select Banner Value</option>";

                    $.each(response['services'], function (index, data) {

                        console.log(data);
                        select += '<option value="' + data['id'] + '">' + data['name'] + '</option>';
                        // $('#banner_value').append('<option value="' + data['id'] + '">' + data['name'] + '</option>');
                    });
                    select +='</select>';
                    $('#banner_value_div').append(select);
                } else if (selectedValue === 'packages') {
                    select += "<select name='banner_value' id='banner_value' class='form-control select2' required='required'> <option value=''>Select Banner Value</option>";

                    $.each(response['bundles'], function (index, data) {
                        console.log(data);
                        select += '<option value="' + data['id'] + '">' + data['name'] + '</option>';
                    });
                    select +='</select>';
                    $('#banner_value_div').append(select);
                }
            },
            error: function(xhr, textStatus, errorThrown){
                // Handle error response
                console.log(errorThrown);
            }
        });
    }else{
        if (selectedValue === 'contact') {
            // console.log(data);
            select += "<select name='banner_value' id='banner_value' class='form-control select2' required='required'>";

            select +='<option value="contact-us">Contact Us Form</option></select>';
            $('#banner_value_div').append(select);
        }
        else if(selectedValue==='link'){
            select += "<input name='banner_value' id='banner_value' class='form-control' required='required' />";


            $('#banner_value_div').append(select);
            // console.log(data);
        }
    }



});

