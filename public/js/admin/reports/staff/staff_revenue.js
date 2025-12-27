/**
 * Created by abdullah@redsignal.biz on 22nd Nov 2018
 */

//== Class definition
var FormControls = function () {
    //== Private functions
    var baseFunction = function () {
        $('.select2').select2();

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

        $('#scheduled_date').daterangepicker({
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
        $('#load_report').html('<i class="fa fa-spin fa-refresh"></i>&nbsp;Load Report').attr('disabled',true);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.staff.revenue.report.load'),
            type: "POST",
            data: {
                date_range: $('#date_range').val(),
                patient_id: $('#patient_id').val(),
                appointment_type_id: $('#appointment_type_id').val(),
                location_id: $('#location_id').val(),
                service_id: $('#service_id').val(),
                user_id: $('#user_id').val(),
                medium_type: $('#medium_type').val(),
                report_type: $('#report_type').val(),
            },
            success: function(response){
                $('#content').html('');
                if($('#medium_type').val() == 'web') {
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
    };

    var loadChart = function (start, end, data) {
        return false;
        var categories = [];
        var total_prices = [];
        var total_qtys = [];
        for(loop =0; loop < data.length; loop++) {
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
        $('#patient_id-report').val($('#patient_id').val());
        $('#appointment_type_id-report').val($('#appointment_type_id').val());
        $('#location_id-report').val($('#location_id').val());
        $('#service_id-report').val($('#service_id').val());
        $('#user_id-report').val($('#user_id').val());
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