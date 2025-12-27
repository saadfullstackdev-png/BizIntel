/*Function to select location against city id*/
$(document).ready(function () {
    $('#city_id_create').on('change',function(){
        var city_id = $(this).val();
        if (city_id != '') {
            $.ajax({
                type: 'get',
                url: route('admin.resourcerotas.load_location'),
                data: {
                    city_id: city_id
                },
                cache: false,
                success: function (response) {
                    if (response.status == '1') {
                        var dropdowndata = '<option value="" selected disabled>Select a Centre</option>';
                        for (var i = 0; i < response.locations.length; i++) {
                            dropdowndata += '<option value="' + response.locations[i].id + '">' + response.locations[i].name + '</option>';
                        }
                        $('#location_id_create').find('option').remove().end().select2({width: '100%'}).append(dropdowndata);
                        resetDropdowns();

                    } else {
                        resetDropdowns();
                    }
                },
            });
        } else {
            resetDropdowns();
        }
    });
    $('#location_id_create').on('change',function(){
        var location_id = $(this).val();
        if (location_id != '') {
            $.ajax({
                type: 'get',
                url: route('admin.resourcerotas.load_doctor_and_Machine'),
                data: {
                    location_id: location_id
                },
                cache: false,
                success: function (response) {
                    if (response.status == '1') {
                        var dropdowndata_doctor = '<option value="" selected disabled>Select a Doctor</option>';

                        for (var key in response.doctors) {
                            dropdowndata_doctor += '<option value="' + key + '">' + response.doctors[key] + '</option>';
                        }
                        $('#resource_doctor_create').find('option').remove().end().select2({width: '100%'}).append(dropdowndata_doctor);

                        var dropdowndata_machine = '<option value="" selected disabled>Select a Machine</option>';
                        for (var i = 0; i < response.machine.length; i++) {
                            dropdowndata_machine += '<option value="' + response.machine[i].id + '">' + response.machine[i].name + '</option>';
                        }
                        $('#resource_machine_create').find('option').remove().end().select2({width: '100%'}).append(dropdowndata_machine);
                    }
                    resetresourcetype();
                },
            });
        } else {
            resetDoctors();
            resetMachine();
        }
    });
});
/*End*/

//Date picker initilize function
$(document).ready(function () {
    var date = new Date();
    date.setDate(date.getDate());
    $('.date_to_rota_1').datepicker({
        format: 'yyyy-mm-dd',
        startDate: date
    }).on('changeDate', function (ev) {
        $(this).datepicker('hide');
    });

    $('.time_to_Rota_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());

    $('.breaktime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);

    $('.monday_breake_time').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null).on('change', function(){

        if ($('#copy_all_1').is(":checked")) {
            $('#copy_all_1').trigger('change');
        }
        return true;
    });

    $('#monday_from').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date()).on('change', function(){

        if ($('#copy_all_1').is(":checked")) {
            $('#copy_all_1').trigger('change');
        }
        return true;
    });
    $('#monday_to').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date()).on('change', function(){

        if ($('#copy_all_1').is(":checked")) {
            $('#copy_all_1').trigger('change');
        }
        return true;
    });
});
/*End*/

//To manage the functionality of Week days
$(document).ready(function () {

    $('#mondayElement_1').on('change', function () {
        if ($('#mondayElement_1').is(':unchecked')) {
            $('#mondayOperation_1 :input').attr('disabled', true);
            $('.mondaytime_1').val('', '');
            $('.monday_breake_time').val('', '');
        } else {
            $('#mondayOperation_1 :input').removeAttr('disabled');
            $('.mondaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.monday_breake_time').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#tuesdayElement_1').on('change', function () {
        if ($('#tuesdayElement_1').is(':unchecked')) {
            $('#tuesdayOperation_1 :input').attr('disabled', true);
            $('.tuesdaytime_1').val('', '');
            $('.tuesdaytime_break').val('', '');
        } else {
            $('#tuesdayOperation_1 :input').removeAttr('disabled');
            $('.tuesdaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.tuesdaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#wednesdayElement_1').on('change', function () {
        if ($('#wednesdayElement_1').is(':unchecked')) {
            $('#wednesdayOperation_1 :input').attr('disabled', true);
            $('.wednesdaytime_1').val('', '');
            $('.wednesdaytime_break').val('', '');
        } else {
            $('#wednesdayOperation_1 :input').removeAttr('disabled');
            $('.wednesdaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.wednesdaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#thursdayElement_1').on('change', function () {
        if ($('#thursdayElement_1').is(':unchecked')) {
            $('#thursdayOperation_1 :input').attr('disabled', true);
            $('.thursdaytime_1').val('', '');
            $('.thursdaytime_break').val('', '');
        } else {
            $('#thursdayOperation_1 :input').removeAttr('disabled');
            $('.thursdaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.thursdaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#fridayElement_1').on('change', function () {
        if ($('#fridayElement_1').is(':unchecked')) {
            $('#fridayOperation_1 :input').attr('disabled', true);
            $('.fridaytime_1').val('', '');
            $('.fridaytime_break').val('', '');
        } else {
            $('#fridayOperation_1 :input').removeAttr('disabled');
            $('.fridaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.fridaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#saturdayElement_1').on('change', function () {
        if ($('#saturdayElement_1').is(':unchecked')) {
            $('#saturdayOperation_1 :input').attr('disabled', true);
            $('.saturdaytime_1').val('', '');
            $('.saturdaytime_break').val('', '');
        } else {
            $('#saturdayOperation_1 :input').removeAttr('disabled');
            $('.saturdaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.saturdaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#sundayElement_1').on('change', function () {
        if ($('#sundayElement_1').is(':unchecked')) {
            $('#sundayOperation_1 :input').attr('disabled', true);
            $('.sundaytime_1').val('', '');
            $('.sundaytime_break').val('', '');
        } else {
            $('#sundayOperation_1 :input').removeAttr('disabled');
            $('.sundaytime_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.sundaytime_break').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#copy_all_1').change(function () {
        if ($(this).is(":checked")) {
            $('#copy_all_1').val('1');

            $('#mondayElement_1').prop('checked', true);

            $('#tuesdayOperation_1 :input').attr('disabled', true);
            $('#tuesdayElement_1').prop('checked', true);

            $('#wednesdayOperation_1 :input').attr('disabled', true);
            $('#wednesdayElement_1').prop('checked', true);

            $('#thursdayOperation_1 :input').attr('disabled', true);
            $('#thursdayElement_1').prop('checked', true);

            $('#fridayOperation_1 :input').attr('disabled', true);
            $('#fridayElement_1').prop('checked', true);

            $('#saturdayOperation_1 :input').attr('disabled', true);
            $('#saturdayElement_1').prop('checked', true);

            $('#sundayOperation_1 :input').attr('disabled', true);
            $('#sundayElement_1').prop('checked', true);

            $('.check_final_1').hide();

            /*get the monday break timing*/
            var frombreakvalue = $('.break_mondayfrom').val();
            var tobreakValue = $('.break_mondayto').val();

            /*set the monday break timing in all days*/
            $(".f_time_break").val(frombreakvalue);
            $(".t_time_break").val(tobreakValue);

            /*get the monday form and to value*/
            var fromValue = $('.mondayfrom_1').val();
            var toValue = $('.mondayto_1').val();

            //set the monday to and from value to all other days
            $(".ftime_1").val(fromValue);
            $(".ttime_1").val(toValue);
        }
        else {
            $('.check_final_1').show();
            $('#copy_all_1').val('0');
            $('#mondayOperation_1 :input').attr('disabled', false);
            $('#tuesdayOperation_1 :input').attr('disabled', false);
            $('#wednesdayOperation_1 :input').attr('disabled', false);
            $('#thursdayOperation_1 :input').attr('disabled', false);
            $('#fridayOperation_1 :input').attr('disabled', false);
            $('#saturdayOperation_1 :input').attr('disabled', false);
            $('#sundayOperation_1 :input').attr('disabled', false);
            $('.check_final_1').show();
            $(".ftime_1").timepicker('setTime', new Date());
            $(".ttime_1").timepicker('setTime', new Date());
            $(".f_time_break").timepicker('setTime', null);
            $(".t_time_break").timepicker('setTime', null);
        }
    });
});
/*End*/

// Jquery function to hide and show the resource type
$(document).ready(function () {
    $('.select2').select2({ width: '100%' });
    $('#resource_type_id').change(function () {
        /*
        * That is use for initilize the null value on change of drop down
        * */
        $('#resource_machine_create').val('');
        $('#resource_doctor_create').val('');

        if ($('#resource_type_id').val() == 'Machine') {
            $('#SelectMachine_create').show();
            $('#SelectDoctor_create').hide();
            $('#Rota_type_operation').hide();
        }
        else {
            $('#SelectDoctor_create').show();
            $('#SelectMachine_create').hide();
            $('#Rota_type_operation').show();
        }
        if($('#resource_type_id').val() == ''){
            $('#SelectDoctor_create').hide();
            $('#SelectMachine_create').hide();
            $('#Rota_type_operation').hide();
        }
    });
    $('.select2').select2({width: '100%'});
});
$('#resource_type_id').change();
/*End*/
/*Reset drop down function*/
var resetDropdowns = function () {
    resetDoctors();
    resetMachine();
    resetresourcetype();
    $("#resource_type_id").val('').trigger('change')
}

var resetDoctors = function () {
    var dropdowndata = '<option value="" selected disabled>Select a Doctor</option>';
    $('#resource_doctor_create').find('option').remove().end().select2().append(dropdowndata);
}
var resetMachine = function () {
    var dropdowndata = '<option value="" selected disabled>Select a Machine</option>';
    $('#resource_machine_create').find('option').remove().end().select2().append(dropdowndata);
}
var resetresourcetype = function () {
    $('#resource_type_id').val('').select2();
    $("#resource_type_id").val('').trigger('change');
}
/*End*/

/*Define query for define rota type for consultancy*/
$('#is_consultancy_1').change(function () {
    if ($(this).is(":checked")) {
        $('#is_consultancy_1').val('1');
    }
    else {
        $('#is_consultancy_1').val('0');
    }
});
$('#is_consultancy_1').change();
/*End*/
/*Define query for define rota type for treatment*/
$('#is_treatment_1').change(function () {
    if ($(this).is(":checked")) {
        $('#is_treatment_1').val('1');
    }
    else {
        $('#is_treatment_1').val('0');
    }
});
$('#is_treatment_1').change();
/*End*/

