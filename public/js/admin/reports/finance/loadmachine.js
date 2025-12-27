function loadmachine() {

    var type_p = $("#report_type").val();
    var location_id = $("#location_id").val();

    if (type_p == "partner_collection_report") {
        $.ajax({
            type: 'get',
            url: route('admin.reports.financereport_report_loadmachine'),
            data: {
                location_id: location_id,
                type_p: type_p,
            },
            cache: false,
            success: function (response) {
                $('#machine').html(response.machinearray).show();
            },
        });
    }
}

function getDiscounts() {
    var type_p = $('#report_type').val();

    if ( type_p == 'account_sales_report' || type_p == 'discount_report') {

        var appointment_type = $('#appointment_type_id').val();

        $.ajax({
            type: 'get',
            url: route('admin.reports.finance_report.getDiscounts'),
            data: {
                appointment_type: appointment_type,
                type_p: type_p,
            },
            cache: false,
            success: function (response) {
                $('#discount').html(response.discounts).show();
            },
        });

    }



}