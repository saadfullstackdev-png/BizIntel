var Home = function () {

    var TableTransform = function () {
        var $table_transform = $('#table-transform');
        $('#transform').click(function () {
            $table_transform.bootstrapTable();
        });
        $('#destroy').click(function () {
            $table_transform.bootstrapTable('destroy');
        });
    }

    var TableStyle = function () {
        var $table_style = $('#table-style');
        // $table_style.bootstrapTable();

        $('#hover, #striped, #condensed').click(function () {
            var classes = 'table';

            if ($('#hover').prop('checked')) {
                classes += ' table-hover';
            }
            if ($('#condensed').prop('checked')) {
                classes += ' table-condensed';
            }
            $('#table-style').bootstrapTable('destroy')
                .bootstrapTable({
                    classes: classes,
                    striped: $('#striped').prop('checked')
                });
        });

        function rowStyle(row, index) {
            var bs_classes = ['active', 'success', 'info', 'warning', 'danger'];

            if (index % 2 === 0 && index / 2 < bs_classes.length) {
                return {
                    classes: bs_classes[index / 2]
                };
            }
            return {};
        }
    }

    var initRevenueByCentre = function ( period ) {

        App.blockUI({
            target: '#dashboard_revenue_by_centre',
            animate: true
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_centre'),
            type: 'GET',
            data: {
                'period':period,
                'performance':'0',
            },
            cache: false,
            success: function (response) {
                App.unblockUI('#dashboard_revenue_by_centre');

                switch (period) {
                    case 'today':
                        generateRevenueChart('location_revenue_today', response.today);
                        break;
                    case 'yesterday':
                        generateRevenueChart('location_revenue_yesterday', response.today);
                        break;
                    case 'last7days':
                        generateRevenueChart('location_revenue_last7days', response.today);
                        break;
                    case 'thismonth':
                        generateRevenueChart('location_revenue_thismonth', response.today);
                        break;
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_revenue_by_centre');
            }
        });
    }

    var initRevenueByService = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#revenue_by_service',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_service'),
            type: 'GET',
            cache: false,
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
            },
            success: function (response) {
                App.unblockUI('#revenue_by_service');
                if (today != '') {
                    generateRevenueChart('service_revenue_today', response.today);
                }
                if (yesterday != '') {
                    generateRevenueChart('service_revenue_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateRevenueChart('service_revenue_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateRevenueChart('service_revenue_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#revenue_by_service');
            }
        });
    }


    var initMyRevenueByCentre = function ( period ) {

        App.blockUI({
            target: '#dashboard_my_revenue_by_centre',
            animate: true,
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_centre'),
            type: 'GET',
            data: {
                'period': period,
                performance: '1'
            },
            cache: false,
            success: function (response) {
                App.unblockUI('#dashboard_my_revenue_by_centre');

                switch (period) {
                    case 'today':
                        generateRevenueChart('my_location_revenue_today', response.today);
                        break;
                    case 'yesterday':
                        generateRevenueChart('my_location_revenue_yesterday', response.today);
                        break;
                    case 'last7days':
                        generateRevenueChart('my_location_revenue_last7days', response.today);
                        break;
                    case 'thismonth':
                        generateRevenueChart('my_location_revenue_thismonth', response.today);
                        break;
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_my_revenue_by_centre');
            }
        });
    }
    var initCollectionByCentre = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#dashboard_collection_by_centre',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.collection_by_centre'),
            type: 'GET',
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
            },
            cache: false,
            success: function (response) {
                App.unblockUI('#dashboard_collection_by_centre');
                if (today != '') {
                    generateRevenueChart('location_collection_today', response.today);
                }
                if (yesterday != '') {
                    generateRevenueChart('location_collection_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateRevenueChart('location_collection_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateRevenueChart('location_collection_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_collection_by_centre');
            }
        });
    }
    var initMyCollectionByCentre = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#dashboard_my_collection_by_centre',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.collection_by_centre'),
            type: 'GET',
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
                'performance': '1'
            },
            cache: false,
            success: function (response) {
                App.unblockUI('#dashboard_my_collection_by_centre');
                if (today != '') {
                    generateRevenueChart('location_my_collection_today', response.today);
                }
                if (yesterday != '') {
                    generateRevenueChart('location_my_collection_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateRevenueChart('location_my_collection_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateRevenueChart('location_my_collection_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_my_collection_by_centre');
            }
        });
    }


    var initMyRevenueByService = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#my_revenue_by_service',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.revenue_by_service'),
            type: 'GET',
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
                'performance': '1',

            },
            cache: false,
            success: function (response) {
                App.unblockUI('#my_revenue_by_service');
                if (today != '') {
                    generateRevenueChart('my_service_revenue_today', response.today);
                }
                if (yesterday != '') {
                    generateRevenueChart('my_service_revenue_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateRevenueChart('my_service_revenue_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateRevenueChart('my_service_revenue_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#my_revenue_by_service');
            }
        });
    }

    var generateRevenueChart = function (id, data) {
        if (typeof(AmCharts) === 'undefined' || $('#' + id).size() === 0) {
            return;
        }



        var chart = AmCharts.makeChart(id, {
            "type": "pie",
            "theme": "light",
            // "path": "../assets/global/plugins/amcharts/ammap/images/",
            "dataProvider": data,
            "valueField": "value",
            "titleField": "centre",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            // "balloonText": "[[title]][[value]] ([[percents]]%)</span>",
            "angle": 0,
            "export": {
                "enabled": true
            }
        });
        console.log(data);
    }

    var initAppointmentsByStatus = function ( period ) {

        App.blockUI({
            target:'#dashboard_appointment_by_status',
            animate:true
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_status'),
            type: 'GET',
            data:{ 'period':period },
            cache: false,
            success: function (response) {
                App.unblockUI('#dashboard_appointment_by_status');

                switch ( period ) {
                    case 'today':
                        generateCountChart('appointment_status_today', response.today);
                        break;

                    case 'yesterday':
                        generateCountChart('appointment_status_yesterday', response.today);
                        break;

                    case 'last7days':
                        generateCountChart('appointment_status_last7days', response.today);
                        break;

                    case 'thismonth':
                        generateCountChart('appointment_status_thismonth', response.today);
                        break;
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_appointment_by_status');
            }
        });
    }

    var initAppointmentsByType = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#appointment_by_type',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_type'),
            type: 'GET',
            cache: false,
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
            },
            success: function (response) {
                App.unblockUI('#appointment_by_type');

                if (today != '') {
                    generateCountChart('appointment_type_today', response.today);
                }
                if (yesterday != '') {
                    generateCountChart('appointment_type_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateCountChart('appointment_type_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateCountChart('appointment_type_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#appointment_by_type');
            }
        });
    }

    var initMyAppointmentsByStatus = function ( period ) {

        App.blockUI({
            target:'#dashboard_my_appointment_by_status',
            animate:true,
        });

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_status'),
            type: 'GET',
            data: {
                'period':period,
                performance: '1'
            },
            cache: false,
            success: function (response) {

                App.unblockUI('#dashboard_my_appointment_by_status');

                switch ( period ) {
                    case 'today':
                        generateCountChart('my_appointment_status_today', response.today);
                        break;

                    case 'yesterday':
                        generateCountChart('my_appointment_status_yesterday', response.today);
                        break;

                    case 'last7days':
                        generateCountChart('my_appointment_status_last7days', response.today);
                        break;

                    case 'thismonth':
                        generateCountChart('my_appointment_status_thismonth', response.today);
                        break;
                }

            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#dashboard_my_appointment_by_status');
            }
        });
    }

    var initMyAppointmentsByType = function (today, yesterday, last7days, thismonth) {
        App.blockUI({
            target: '#my_appointment_by_type',
            animate: true
        });
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.dashboard.appointment_by_type'),
            type: 'GET',
            data: {
                'today': today,
                'yesterday': yesterday,
                'last7days': last7days,
                'thismonth': thismonth,
                'performance': '1'
            },
            cache: false,
            success: function (response) {
                App.unblockUI('#my_appointment_by_type');
                if (today != '') {
                    generateCountChart('my_appointment_type_today', response.today);
                }
                if (yesterday != '') {
                    generateCountChart('my_appointment_type_yesterday', response.yesterday);
                }

                if (last7days != '') {
                    generateCountChart('my_appointment_type_last7days', response.last7days);
                }
                if (thismonth != '') {
                    generateCountChart('my_appointment_type_thismonth', response.thismonth);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                App.unblockUI('#my_appointment_by_type');
            }
        });
    }

    var generateCountChart = function (id, data) {
        if (typeof(AmCharts) === 'undefined' || $('#' + id).size() === 0) {
            return;
        }

        var chart = AmCharts.makeChart(id, {
            "type": "pie",
            "theme": "light",
            // "path": "../assets/global/plugins/amcharts/ammap/images/",
            "dataProvider": data,
            "valueField": "value",
            "titleField": "appointment",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            // "balloonText": "[[title]] <span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>",
            "angle": 0,
            "export": {
                "enabled": true
            }
        });
    }
    return {

        //main function to initiate the module
        init: function () {

            TableTransform();
            TableStyle();
        },
        initRevenueByService: initRevenueByService,
        initRevenueByCentre: initRevenueByCentre,
        initCollectionByCentre: initCollectionByCentre,
        initMyCollectionByCentre: initMyCollectionByCentre,
        initMyRevenueByService: initMyRevenueByService,
        initMyRevenueByCentre: initMyRevenueByCentre,

        initAppointmentsByStatus: initAppointmentsByStatus,
        initAppointmentsByType: initAppointmentsByType,
        initMyAppointmentsByStatus: initMyAppointmentsByStatus,
        initMyAppointmentsByType: initMyAppointmentsByType,

        initMyCollectionByCentre: initMyCollectionByCentre,

    };
}();

jQuery(document).ready(function () {
    Home.init();
});