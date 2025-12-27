var FormControls = function () {
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
        $('.dob').datepicker({
            format: 'yyyy-mm-dd',
        }).on('changeDate', function (ev) {
            $(this).datepicker('hide');
        });
        $("#cnic").inputmask("99999-9999999-9", {
            placeholder: "XXXXX-XXXXXXX-X",
            clearMaskOnLostFocus: true
        });
    }
    var loadReport = function () {
        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled',true);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.rbreakup_report_load'),
            type: "POST",
            data: {
                date_range: $('#date_range').val(),
                region_id: $('#region_id').val(),
                role_id: $('#role_id').val(),
                user_id: $('#user_id').val(),
                medium_type: $('#medium_type').val(),
                location_id: $("#location_id").val(),
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
        $('#region_id-report').val($('#region_id').val());
        $('#role_id-report').val($('#role_id').val());
        $('#user_id-report').val($('#user_id').val());
        $('#location_id-report').val($('#location_id').val());
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