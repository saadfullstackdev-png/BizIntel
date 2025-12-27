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
            // "parentEl":".input-group",
            locale: {
                // cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('month'), moment().subtract(1, 'year').endOf('year')],
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        });

        $('#scheduled_date').daterangepicker({
            "alwaysShowCalendars": true,
            locale: {
                // cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('month'), moment().subtract(1, 'year').endOf('year')],
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        });

        $('input[name="date_range"]').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="date_range"]').on('cancel.daterangepicker', function (ev, picker) {
        });
    }

    var loadReport = function () { 
        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled', true);
    
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('patient_id', $('#patient_id').val() || '');
        formData.append('scheduled_date', $('#scheduled_date').val() || '');
        formData.append('doctor_id', $('#doctor_id').val() || '');
        formData.append('region_id', $('#region_id').val() || '');
        formData.append('service_id', $('#service_id').val() || '');
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');
        formData.append('consultancy_type', $('#consultancy_type').val() || '');
        formData.append('user_id', $('#user_id').val() || '');
        formData.append('re_user_id', $('#re_user_id').val() || '');
        formData.append('referred_by', $('#referred_by').val() || '');
        formData.append('is_converted', $('#is_converted').val() || '');
        formData.append('up_user_id', $('#up_user_id').val() || '');
        formData.append('medium_type', $('#medium_type').val());
        formData.append('city_id',$('#city_id').val());
        // alert($formData);
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.summary_report_load'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false, 
            success: function (response) {
                $('#content').html('');
                if ($('#medium_type').val() === 'web') {
                    $('#content').html(response);
                }
                $('#load_report').html('Load Report').removeAttr('disabled');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.error("Error loading report:", thrownError);
                $('#load_report').html('Load Report').removeAttr('disabled');
            }
        });
    };

    var loadleadReport = function () { 
        $('#load_lead_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Lead Report').attr('disabled', true);
    
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('patient_id', $('#patient_id').val() || '');
        formData.append('scheduled_date', $('#scheduled_date').val() || '');
        formData.append('doctor_id', $('#doctor_id').val() || '');
        formData.append('region_id', $('#region_id').val() || '');
        formData.append('service_id', $('#service_id').val() || '');
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');
        formData.append('consultancy_type', $('#consultancy_type').val() || '');
        formData.append('user_id', $('#user_id').val() || '');
        formData.append('re_user_id', $('#re_user_id').val() || '');
        formData.append('referred_by', $('#referred_by').val() || '');
        formData.append('is_converted', $('#is_converted').val() || '');
        formData.append('up_user_id', $('#up_user_id').val() || '');
        formData.append('medium_type', $('#medium_type').val());
        formData.append('city_id',$('#city_id').val());
        // alert($formData);
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.summary_report_lead_load'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false, 
            success: function (response) {
                $('#content').html('');
                if ($('#medium_type').val() === 'web') {
                    $('#content').html(response);
                }
                $('#load_report').html('Load Report').removeAttr('disabled');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.error("Error loading report:", thrownError);
                $('#load_report').html('Load Report').removeAttr('disabled');
            }
        });
    };

    var loadconversionReport = function () { 
        $('#load_conversion_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Conversion Report').attr('disabled', true);
    
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('patient_id', $('#patient_id').val() || '');
        formData.append('scheduled_date', $('#scheduled_date').val() || '');
        formData.append('doctor_id', $('#doctor_id').val() || '');
        formData.append('region_id', $('#region_id').val() || '');
        formData.append('service_id', $('#service_id').val() || '');
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');
        formData.append('consultancy_type', $('#consultancy_type').val() || '');
        formData.append('user_id', $('#user_id').val() || '');
        formData.append('re_user_id', $('#re_user_id').val() || '');
        formData.append('referred_by', $('#referred_by').val() || '');
        formData.append('is_converted', $('#is_converted').val() || '');
        formData.append('up_user_id', $('#up_user_id').val() || '');
        formData.append('medium_type', $('#medium_type').val());
        formData.append('city_id',$('#city_id').val());
        // alert($formData);
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.bookings_arrivals_conversions_report_load'),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false, 
            success: function (response) {
                $('#content').html('');
                if ($('#medium_type').val() === 'web') {
                    $('#content').html(response);
                }
                $('#load_report').html('Load Report').removeAttr('disabled');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.error("Error loading report:", thrownError);
                $('#load_report').html('Load Report').removeAttr('disabled');
            }
        });
    };

    var loadChart = function (start, end, data) {
        return false;
        var categories = [];
        var total_prices = [];
        var total_qtys = [];
        for (loop = 0; loop < data.length; loop++) {
            categories.push(data[loop].sale_date + ' - ' + data[loop].customer_name);
            total_prices.push(parseFloat(data[loop].total_price));
            total_qtys.push(parseInt(data[loop].total_qty));
        }

        Highcharts.chart('content', {
            chart: {
                type: 'bar'
            },
            title: {
                text: 'Sale Report by Customer'
            },
            xAxis: {
                categories: categories,
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Population (millions)',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: ''
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 80,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Saled Qty',
                data: total_qtys
            }, {
                name: 'Saled Price',
                data: total_prices
            }]
        });
    }

    var printReport = function (medium_type) {
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('city_id', $('#city_id').val() || '');  // Use the actual value from the DOM
        formData.append('patient_id', $('#patient_id').val() || '');  // Adjust as needed
        formData.append('scheduled_date', $('#scheduled_date').val() || '');  // Adjust as needed
        formData.append('doctor_id', $('#doctor_id').val() || '');  // Adjust as needed
        formData.append('region_id', $('#region_id').val() || '');  // Adjust as needed
        formData.append('service_id', $('#service_id').val() || '');  // Adjust as needed
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');  // Adjust as needed
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');  // Adjust as needed
        formData.append('consultancy_type', $('#consultancy_type').val() || '');  // Adjust as needed
        formData.append('user_id', $('#user_id').val() || '');  // Adjust as needed
        formData.append('re_user_id', $('#re_user_id').val() || '');  // Adjust as needed
        formData.append('referred_by', $('#referred_by').val() || '');  // Adjust as needed
        formData.append('is_converted', $('#is_converted').val() || '');  // Adjust as needed
        formData.append('up_user_id', $('#up_user_id').val() || '');  // Adjust as needed
        // formData.append('city_id', id || '');
        // formData.append('patient_id', id || '');
        // formData.append('scheduled_date', id || '');
        // formData.append('doctor_id', id || '');
        // formData.append('region_id', id || '');
        // formData.append('service_id', id || '');
        // formData.append('appointment_status_id', id || '');
        // formData.append('appointment_type_id', id || '');
        // formData.append('consultancy_type', id|| '');
        // formData.append('user_id', id || '');
        // formData.append('re_user_id', id || '');
        // formData.append('referred_by', id || '');
        // formData.append('is_converted', id || '');
        // formData.append('up_user_id', id || '');
        // formData.append('medium_type', id || '');
        // formData.append('city_id',$('#city_id').val() || '');
        formData.append('medium_type', medium_type);
    
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        let tempForm = $('<form>', {
            'method': 'POST',
            'action': route('admin.reports.summary_report_load'),
            'target': '_blank'
        });
    
        formData.forEach((value, key) => {
            $('<input>').attr({
                'type': 'hidden',
                'name': key,
                'value': value
            }).appendTo(tempForm);
        });
    
        // Append and submit the form, then remove it
        tempForm.appendTo('body').submit().remove();
    }
    var printleadReport = function (medium_type) {
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('city_id', $('#city_id').val() || '');  // Use the actual value from the DOM
        formData.append('patient_id', $('#patient_id').val() || '');  // Adjust as needed
        formData.append('scheduled_date', $('#scheduled_date').val() || '');  // Adjust as needed
        formData.append('doctor_id', $('#doctor_id').val() || '');  // Adjust as needed
        formData.append('region_id', $('#region_id').val() || '');  // Adjust as needed
        formData.append('service_id', $('#service_id').val() || '');  // Adjust as needed
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');  // Adjust as needed
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');  // Adjust as needed
        formData.append('consultancy_type', $('#consultancy_type').val() || '');  // Adjust as needed
        formData.append('user_id', $('#user_id').val() || '');  // Adjust as needed
        formData.append('re_user_id', $('#re_user_id').val() || '');  // Adjust as needed
        formData.append('referred_by', $('#referred_by').val() || '');  // Adjust as needed
        formData.append('is_converted', $('#is_converted').val() || '');  // Adjust as needed
        formData.append('up_user_id', $('#up_user_id').val() || '');  // Adjust as needed
        // formData.append('city_id', id || '');
        // formData.append('patient_id', id || '');
        // formData.append('scheduled_date', id || '');
        // formData.append('doctor_id', id || '');
        // formData.append('region_id', id || '');
        // formData.append('service_id', id || '');
        // formData.append('appointment_status_id', id || '');
        // formData.append('appointment_type_id', id || '');
        // formData.append('consultancy_type', id|| '');
        // formData.append('user_id', id || '');
        // formData.append('re_user_id', id || '');
        // formData.append('referred_by', id || '');
        // formData.append('is_converted', id || '');
        // formData.append('up_user_id', id || '');
        // formData.append('medium_type', id || '');
        // formData.append('city_id',$('#city_id').val() || '');
        formData.append('medium_type', medium_type);
    
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        let tempForm = $('<form>', {
            'method': 'POST',
            'action': route('admin.reports.summary_report_lead_load'),
            'target': '_blank'
        });
    
        formData.forEach((value, key) => {
            $('<input>').attr({
                'type': 'hidden',
                'name': key,
                'value': value
            }).appendTo(tempForm);
        });
    
        // Append and submit the form, then remove it
        tempForm.appendTo('body').submit().remove();
    }
    var printconversionReport = function (medium_type) {
        let formData = new FormData();
        formData.append('date_range', $('#date_range').val());
        formData.append('date_range_by', $('#date_range_by').val());
        formData.append('date_range_by_first', $('#date_range_by_first').val());
        formData.append('city_id', $('#city_id').val() || '');  // Use the actual value from the DOM
        formData.append('patient_id', $('#patient_id').val() || '');  // Adjust as needed
        formData.append('scheduled_date', $('#scheduled_date').val() || '');  // Adjust as needed
        formData.append('doctor_id', $('#doctor_id').val() || '');  // Adjust as needed
        formData.append('region_id', $('#region_id').val() || '');  // Adjust as needed
        formData.append('service_id', $('#service_id').val() || '');  // Adjust as needed
        formData.append('appointment_status_id', $('#appointment_status_id').val() || '');  // Adjust as needed
        formData.append('appointment_type_id', $('#appointment_type_id').val() || '');  // Adjust as needed
        formData.append('consultancy_type', $('#consultancy_type').val() || '');  // Adjust as needed
        formData.append('user_id', $('#user_id').val() || '');  // Adjust as needed
        formData.append('re_user_id', $('#re_user_id').val() || '');  // Adjust as needed
        formData.append('referred_by', $('#referred_by').val() || '');  // Adjust as needed
        formData.append('is_converted', $('#is_converted').val() || '');  // Adjust as needed
        formData.append('up_user_id', $('#up_user_id').val() || '');  // Adjust as needed
        // formData.append('city_id', id || '');
        // formData.append('patient_id', id || '');
        // formData.append('scheduled_date', id || '');
        // formData.append('doctor_id', id || '');
        // formData.append('region_id', id || '');
        // formData.append('service_id', id || '');
        // formData.append('appointment_status_id', id || '');
        // formData.append('appointment_type_id', id || '');
        // formData.append('consultancy_type', id|| '');
        // formData.append('user_id', id || '');
        // formData.append('re_user_id', id || '');
        // formData.append('referred_by', id || '');
        // formData.append('is_converted', id || '');
        // formData.append('up_user_id', id || '');
        // formData.append('medium_type', id || '');
        // formData.append('city_id',$('#city_id').val() || '');
        formData.append('medium_type', medium_type);
    
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
        let locationIds = $('#location_id').val();
        if (Array.isArray(locationIds)) {
            locationIds.forEach(id => {
                formData.append('location_id[]', id);
            });
        }
    
        let leadSourceIds = $('#lead_source_id').val();
        if (Array.isArray(leadSourceIds)) {
            leadSourceIds.forEach(id => {
                formData.append('lead_source_id[]', id);
            });
        }
    
        let tempForm = $('<form>', {
            'method': 'POST',
            'action': route('admin.reports.bookings_arrivals_conversions_report_load'),
            'target': '_blank'
        });
    
        formData.forEach((value, key) => {
            $('<input>').attr({
                'type': 'hidden',
                'name': key,
                'value': value
            }).appendTo(tempForm);
        });
    
        // Append and submit the form, then remove it
        tempForm.appendTo('body').submit().remove();
    }

    return {
        // public functions
        init: function () {
            baseFunction();
        },
        loadReport: loadReport,
        loadleadReport: loadleadReport,
        loadconversionReport: loadconversionReport,
        printReport: printReport,
        printleadReport: printleadReport,
        printconversionReport: printconversionReport,
    };
}();

jQuery(document).ready(function () {
    FormControls.init();
});

//Top fake scrollbar for report tables

