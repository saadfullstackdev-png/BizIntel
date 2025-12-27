$(document).ready(function () {
    var date = new Date();
    date.setDate(date.getDate());
    $('.date_to_rota').datepicker({
        format: 'yyyy-mm-dd',
        startDate: date
    }).on('changeDate', function(ev){
        $(this).datepicker('hide');
    })
    $('.time_to_Rota').timepicker({timeFormat: 'h:mm:ss p'});

    var week_days = ['monday','tuesday','wednesday','thursday', 'friday', 'saturday', 'sunday'];

    $.each(week_days, function (key, day) {
        if($('#break_from_update_' + day).val()) {
            $('#break_from_update_'+day).timepicker({timeFormat: 'h:mm:ss p'});
            $('#break_to_update_'+day).timepicker({timeFormat: 'h:mm:ss p'});
        }else{
            $('#break_from_update_'+day).timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
            $('#break_to_update_'+day).timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('.monday_breake_time_1').timepicker({timeFormat: 'h:mm:ss p'}).on('change', function(){

        if ($('#copy_all').is(":checked")) {
            $('#copy_all').trigger('change');
        }
        return true;
    });

    $('#monday_from_update').timepicker({timeFormat: 'h:mm:ss p'}).on('change', function(){
        if ($('#copy_all').is(":checked")) {
            $('#copy_all').trigger('change');
        }
        return true;
    });
    $('#monday_to_update').timepicker({timeFormat: 'h:mm:ss p'}).on('change', function(){
        if ($('#copy_all').is(":checked")) {
            $('#copy_all').trigger('change');
        }
        return true;
    });
});
$(document).ready(function() {
    $('#mondayElement').on('change', function () {
        if ($('#mondayElement').is(':unchecked')) {
            $('#mondayOperation :input').attr('disabled', true);
            $('.mondaytime').val('','');
            $('.monday_breake_time_1').val('', '');
        } else {
            $('#mondayOperation :input').removeAttr('disabled');
            $('.mondaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.monday_breake_time_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#tuesdayElement').on('change', function () {
        if ($('#tuesdayElement').is(':unchecked')) {
            $('#tuesdayOperation :input').attr('disabled', true);
            $('.tuesdaytime').val('','');
            $('.tuesdaytime_break_1').val('', '');
            console.log("Unchecked");
        } else {
            $('#tuesdayOperation :input').removeAttr('disabled');
            $('.tuesdaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.tuesdaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
            console.log("checked");
        }
    });
    $('#wednesdayElement').on('change', function () {
        if ($('#wednesdayElement').is(':unchecked')) {
            $('#wednesdayOperation :input').attr('disabled', true);
            $('.wednesdaytime').val('','');
            $('.wednesdaytime_break_1').val('', '');

        } else {
            $('#wednesdayOperation :input').removeAttr('disabled');
            $('.wednesdaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.wednesdaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);

        }
    });
    $('#thursdayElement').on('change', function () {
        if ($('#thursdayElement').is(':unchecked')) {
            $('#thursdayOperation :input').attr('disabled', true);
            $('.thursdaytime').val('','');
            $('.thursdaytime_break_1').val('', '');
        } else {
            $('#thursdayOperation :input').removeAttr('disabled');
            $('.thursdaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.thursdaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);

        }
    });
    $('#fridayElement').on('change', function () {
        if ($('#fridayElement').is(':unchecked')) {
            $('#fridayOperation :input').attr('disabled', true);
            $('.fridaytime').val('','');
            $('.fridaytime_break_1').val('', '');
        } else {
            $('#fridayOperation :input').removeAttr('disabled');
            $('.fridaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.fridaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#saturdayElement').on('change', function () {
        if ($('#saturdayElement').is(':unchecked')) {
            $('#saturdayOperation :input').attr('disabled', true);
            $('.saturdaytime').val('','');
            $('.saturdaytime_break_1').val('', '');
        } else {
            $('#saturdayOperation :input').removeAttr('disabled');
            $('.saturdaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.saturdaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
    $('#sundayElement').on('change', function () {
        if ($('#sundayElement').is(':unchecked')) {
            $('#sundayOperation :input').attr('disabled', true);
            $('.sundaytime').val('','');
            $('.sundaytime_break_1').val('', '');
        } else {
            $('#sundayOperation :input').removeAttr('disabled');
            $('.sundaytime').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", new Date());
            $('.sundaytime_break_1').timepicker({timeFormat: 'h:mm:ss p'}).timepicker("setTime", null);
        }
    });
});
$(document).ready(function() {

    $('#copy_all').on('change',function(){
        if($(this).is(":checked")) {
            $('#copy_all').val('1');

            $('#mondayElement').prop('checked', true);

            $('#tuesdayOperation :input').attr('disabled',true);
            $('#tuesdayElement').prop('checked', true);

            $('#wednesdayOperation :input').attr('disabled',true);
            $('#wednesdayElement').prop('checked', true);

            $('#thursdayOperation :input').attr('disabled',true);
            $('#thursdayElement').prop('checked', true);

            $('#fridayOperation :input').attr('disabled',true);
            $('#fridayElement').prop('checked', true);

            $('#saturdayOperation :input').attr('disabled',true);
            $('#saturdayElement').prop('checked', true);

            $('#sundayOperation :input').attr('disabled',true);
            $('#sundayElement').prop('checked', true);
            $('.check_final').hide();

            /*get the monday break timing*/
            var frombreakvalue = $('.break_mondayfrom_1').val();
            console.log("Monday Break Time from "+frombreakvalue);
            var tobreakValue = $('.break_mondayto_1').val();
            console.log("Monday Break Time To "+tobreakValue);

            /*set the monday break timing in all days*/
            $(".f_time_break_1").val(frombreakvalue);
            $(".t_time_break_1").val(tobreakValue);

            /*get the monday form and to value*/
            var fromValue =$('.mondayfrom').val();
            console.log("Monday from" + fromValue);
            var toValue =$('.mondayto').val();
            console.log("Monday To" + toValue);


            //set the monday to and from value to all other days
            $(".ftime").val(fromValue);
            $(".ttime").val(toValue);

        } else {
            console.log('B');
            $('.check_final').show();
            $('#copy_all').val('0');

            $('#mondayOperation :input').attr('disabled', false);
            $('#tuesdayOperation :input').attr('disabled', false);
            $('#wednesdayOperation :input').attr('disabled', false);
            $('#thursdayOperation :input').attr('disabled', false);
            $('#fridayOperation :input').attr('disabled', false);
            $('#saturdayOperation :input').attr('disabled', false);
            $('#sundayOperation :input').attr('disabled', false);


            if($('#mondayElement').is(':unchecked')){
                $('#mondayOperation :input').attr('disabled', true);
                $('#mondayOperation :input').val('');
            }
            if($('#tuesdayElement').is(':unchecked')){
                $('#tuesdayOperation :input').attr('disabled', true);
                $('#tuesdayOperation :input').val('');
            }
            if($('#wednesdayElement').is(':unchecked')){
                $('#wednesdayOperation :input').attr('disabled', true);
                $('#wednesdayOperation :input').val('');
            }
            if( $('#thursdayElement').is(':unchecked')){
                $('#thursdayOperation :input').attr('disabled', true);
                $('#thursdayOperation :input').val('');
            }
            if($('#fridayElement').is(':unchecked')){
                $('#fridayOperation :input').attr('disabled', true);
                $('#fridayOperation :input').val('');
            }
            if($('#saturdayElement').is(':unchecked')){
                $('#saturdayOperation :input').attr('disabled', true);
                $('#saturdayOperation :input').val('');
            }
            if($('#sundayElement').is(':unchecked')){
                $('#sundayOperation :input').attr('disabled', true);
                $('#sundayOperation :input').val('');
            }
            if(
                $('#mondayElement').is(':checked') ||
                $('#tuesdayElement').is(':checked') ||
                $('#wednesdayElement').is(':checked') ||
                $('#thursdayElement').is(':checked') ||
                $('#fridayElement').is(':checked') ||
                $('#saturdayElement').is(':checked') ||
                $('#sundayElement').is(':checked')
            ) {

            } else {

                $(".ftime").timepicker('setTime', new Date());
                $(".ttime").timepicker('setTime', new Date());
            }
        }
    });
    $('#copy_all').change();
    $('.select2').select2({ width: '100%' });
});
/*Define query for define rota type for consultancy*/
$('#is_consultancy').change(function () {
    if ($(this).is(":checked")) {
        $('#is_consultancy').val('1');
    }
    else {
        $('#is_consultancy').val('0');
    }
});
/*End*/
/*Define query for define rota type for treatment*/
$('#is_treatment').change(function () {
    if ($(this).is(":checked")) {
        $('#is_treatment').val('1');
    }
    else {
        $('#is_treatment').val('0');
    }
});
/*End*/
