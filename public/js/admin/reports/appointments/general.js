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
            // $(this).val('');
        });
    }

    var loadReport = function () {
        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled', true);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.appointments_general_load'),
            type: "POST",
            data: {
                date_range: $('#date_range').val(),
                date_range_by: $('#date_range_by').val(),
                date_range_by_first: $('#date_range_by_first').val(),
                patient_id: $('#patient_id').val(),
                scheduled_date: $('#scheduled_date').val(),
                doctor_id: $('#doctor_id').val(),
                city_id: $('#city_id').val(),
                region_id: $('#region_id').val(),
                location_id: $('#location_id').val(),
                service_id: $('#service_id').val(),
                appointment_status_id: $('#appointment_status_id').val(),
                appointment_type_id: $('#appointment_type_id').val(),
                consultancy_type: $('#consultancy_type').val(),
                user_id: $('#user_id').val(),
                re_user_id: $('#re_user_id').val(),
                up_user_id: $('#up_user_id').val(),
                referred_by: $('#referred_by').val(),
                medium_type: $('#medium_type').val(),
                report_type: $('#report_type').val(),
                lead_sources_id: $('#lead_sources_id').val(),
                is_converted: $('#is_converted').val()

            },
            success: function (response) {
                $('#content').html('');
                if ($('#medium_type').val() == 'web') {
                    $('#content').html(response);
                } else {
                    return false;
                    // loadChart(response.start_date, response.end_date, response.SaleData);
                }
                $('#load_report').html('Load Report').removeAttr('disabled');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('#load_report').html('Load Report').removeAttr('disabled');
                return false;
            }
        });
    }

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
        $('#date_range-report').val($('#date_range').val());
        $('#date_range_by-report').val($('#date_range_by').val());
        $('#date_range_by_first-report').val($('#date_range_by_first').val());
        $('#patient_id-report').val($('#patient_id').val());
        $('#scheduled_date-report').val($('#scheduled_date').val());
        $('#doctor_id-report').val($('#doctor_id').val());
        $('#city_id-report').val($('#city_id').val());
        $('#region_id-report').val($('#region_id').val());
        $('#location_id-report').val($('#location_id').val());
        $('#service_id-report').val($('#service_id').val());
        $('#appointment_status_id-report').val($('#appointment_status_id').val());
        $('#appointment_type_id-report').val($('#appointment_type_id').val());
        $('#consultancy_type-report').val($('#consultancy_type').val());
        $('#user_id-report').val($('#user_id').val());
        $('#re_user_id-report').val($('#re_user_id').val());
        $('#up_user_id-report').val($('#up_user_id').val());
        $('#referred_by-report').val($('#referred_by').val());
        $('#medium_type-report').val(medium_type);
        $('#report_type-report').val($('#report_type').val());
        $('#is_converted-report').val($('#is_converted').val());
        $('#report-form').submit();
    }

    return {
        // public functions
        init: function () {
            baseFunction();
        },
        loadReport: loadReport,
        printReport: printReport,
    };
}();

jQuery(document).ready(function () {
    FormControls.init();
});

//Top fake scrollbar for report tables

