var FormControls = function () {
    var selectedUserValue = $('#user_id').val();
    $('.doctor_id').hide();
    $('.app_user_id').hide();
    $("#staff_type").change(function () {
        var selected_option = $('#staff_type').val();
//----------------------- All Staff ----------------------------------
        if (selected_option == '') {
            //$('#fnivel2').attr('pk','1').show();
            //console.log('All Staff Selected');
            $('.user_id').show();
            $('.doctor_id').hide();
            $('.app_user_id').hide();
        }
//----------------------- Admin & Application User Staff ----------------------------------
        if (selected_option == 2) {
            //$("#fnivel2").removeAttr('pk').hide();
            //console.log('Application User Selected');
            $('.user_id').hide();
            $('.doctor_id').hide();
            $('.app_user_id').show();
        }
//----------------------- Doctor / Practitioner Staff ----------------------------------
        if (selected_option == 5) {
            //$("#fnivel2").removeAttr('pk').hide();
            //console.log('Doctor Selected');
            $('.user_id').hide();
            $('.doctor_id').show();
            $('.app_user_id').hide();
        }
    });

    var baseFunction = function () {
        // To make Pace works on Ajax calls
        $('.select2').select2({ width: '100%' });
        $('#date_range').daterangepicker({
            "alwaysShowCalendars": true,
            locale: {
                // cancelLabel: 'Clear'
            },
            ranges   : {
                'Today'       : [moment(), moment()],
                'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year'  : [moment().startOf('year'), moment().endOf('year')],
                'Last Year'  : [moment().subtract(1, 'year').startOf('month'), moment().subtract(1, 'year').endOf('year')],
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
        });

        $('input[name="date_range"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="date_range"]').on('cancel.daterangepicker', function(ev, picker) {
            // $(this).val('');
        });
    }
    var loadReport = function () {
        if($('.user_id').is(":visible"))
        {
            selectedUserValue = $('#user_id').val();
        }
        if($('.doctor_id').is(":visible"))
        {
            selectedUserValue = $('#doctor_id').val();
        }
        if($('.app_user_id').is(":visible"))
        {
            selectedUserValue = $('#app_user_id').val();
        }
        //console.log('selectedUserValue: '+ selectedUserValue);
        //console.log($('#user_id').val());
        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled',true);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.staff.reports.load'),
            type: "POST",
            data: {
                date_range: $('#date_range').val(),
                staff_type: $('#staff_type').val(),
//                user_id: $('#user_id').val(),
                user_id: selectedUserValue,
                email: $('#email').val(),
                gender_id: $('#gender_id').val(),
                age_group_range: $('#age_group_range').val(),
                region_id: $('#region_id').val(),
                location_id: $('#location_id').val(),
                city_id: $('#city_id').val(),
                service_id: $('#service_id').val(),
                report_type : $('#report_type').val(),
                telecomprovider_id : $('#telecomprovider_id').val(),
                phone: $('#phone').val(),
                medium_type: $('#medium_type').val(),

            },
            success: function(response){
                $('#content').html('');
                $('#content').html(response);
                $('#load_report').html('Load Report').removeAttr('disabled');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#load_report').html('Load Report').removeAttr('disabled');
                return false;
            }
        });
    }
    var printReport = function (medium_type) {
        $('#date_range-report').val($('#date_range').val());
        $('#staff_type-report').val($('#staff_typed').val());
        //$('#user_id-report').val($('#user_id').val());
        $('#user_id-report').val(selectedUserValue);
        $('#email-report').val($('#email').val());
        $('#gender_id-report').val($('#gender_id').val());
        $('#age_group_range-report').val($('#age_group_range').val());
        $('#region_id-report').val($('#region_id').val());
        $('#location_id-report').val($('#location_id').val());
        $('#city_id-report').val($('#city_id').val());
        $('#service_id-report').val($('#service_id').val());
        $('#report_type-report').val($('#report_type').val());
        $('#telecomprovider_id-report').val($('#telecomprovider_id').val());
        $('#phone-report').val($('#phone').val());
        $('#medium_type-report').val(medium_type);
        $('#report-form').submit();
    }

    return {
        // public functions
        init: function() {
            baseFunction();
        },
        loadReport: loadReport,
        printReport: printReport,
    };
}();

jQuery(document).ready(function() {
    FormControls.init();
});