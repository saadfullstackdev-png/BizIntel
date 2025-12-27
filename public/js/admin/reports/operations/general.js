/**
 * Created by mustafa.mughal on 12/7/2017.
 */

//== Class definition
var FormControls = function () {
    //== Private functions

    var baseFunction = function () {
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
        }).on('cancel.daterangepicker', function(ev, picker) {

        });
    }

    var loadReport = function () {

        console.log($('#day_number').val());

        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled',true);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.operations_report_load'),
            type: "POST",
            data: {
                date_range: $('#date_range').val(),
                date_range_by: $('#date_range_by').val(),
                date_range_by_first: $('#date_range_by_first').val(),
                year: $('#year').val(),
                month: $('#month').val(),
                days_count: $('#days_count').val(),
                completed_working_days: $('#completed_working_days').val(),
                region_id: $('#region_id').val(),
                city_id: $('#city_id').val(),
                location_id: $('#location_id').val(),
                service_id: $('#service_id').val(),
                user_id: $('#user_id').val(),
                type: $('#type').val(),
                consultancy_type: $('#consultancy_type').val(),
                appointment_type_id: $('#appointment_type_id').val(),
                patient_id: $('#patient_id').val(),
                medium_type: $('#medium_type').val(),
                report_type: $('#report_type').val(),
            },
            success: function(response){
                $('#content').html('');
                if($('#medium_type').val() == 'web') {
                    $('#content').html(response);
                } else {
                    return false;
                }
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
        $('#date_range_by-report').val($('#date_range_by').val());
        $('#date_range_by_first-report').val($('#date_range_by_first').val());
        $('#year-report').val($('#year').val());
        $('#month-report').val($('#month').val());
        $('#days_count-report').val($('#days_count').val());
        $('#completed_working_days-report').val($('#completed_working_days').val());
        $('#region_id-report').val($('#region_id').val());
        $('#city_id-report').val($('#city_id').val());
        $('#location_id-report').val($('#location_id').val());
        $('#service_id-report').val($('#service_id').val());
        $('#user_id-report').val($('#user_id').val());
        $('#type-report').val($('#type').val());
        $('#consultancy_type-report').val($('#consultancy_type').val());
        $('#appointment_type_id-report').val($('#appointment_type_id').val());
        $('#patient_id-report').val($('#patient_id').val());
        $('#medium_type-report').val(medium_type);
        $('#report_type-report').val($('#report_type').val());
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