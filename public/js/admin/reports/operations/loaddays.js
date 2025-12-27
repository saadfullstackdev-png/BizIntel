function LoadDaysArray() {
    var type_p = $("#report_type").val();
    var year = $('#year').val();
    var month = $('#month').val();
    if (type_p == "center_target_report" && year && month || type_p == 'operations_company_health' && month && year) {
        $.ajax({
            type: 'get',
            url: route('admin.reports.operations_report_loadday'),
            data: {
                year: year,
                month: month,
            },
            cache: false,
            success: function (response) {
                $('#day_number').html(response.daysarray).show();
            },
        });
    }
}