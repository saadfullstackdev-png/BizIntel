var FormControls = function () {
    var excelReport = function (medium_type) {
        console.log("I am here");
        $('#patient_id_report').val($("#patient_id").val());
        $('#name_report').val($("#name").val());
        $('#phone_report').val($("#phone").val());
        $('#date_from_report').val($("#date_from").val());
        $('#date_to_report').val($("#date_to").val());
        $('#doctor_id_report').val($("#doctor_id").val());
        $('#region_id_report').val($("#region_id").val());
        $('#city_id_report').val($("#city_id").val());
        $('#town_id_report').val($("#town_id").val());
        $('#location_id_report').val($("#location_id").val());
        $('#lead_source_id_report').val($("#lead_source_id").val());
        $('#service_id_report').val($("#service_id").val());
        $('#appointment_status_id_report').val($("#appointment_status_id").val());
        $('#appointment_type_id_report').val($("#appointment_type_id").val());
        $('#consultancy_type_report').val($("#consultancy_type").val());
        $('#created_from_report').val($("#created_from").val());
        $('#created_to_report').val($("#created_to").val());
        $('#created_by_report').val($("#created_by").val());
        $('#converted_by_report').val($("#converted_by").val());
        $('#updated_by_report').val($("#updated_by").val());
        $('#report-form').submit();
    }
    return {
        // public functions
        init: function() {},
        excel_Report: excelReport,
    };
}();
jQuery(document).ready(function() {
    FormControls.init();
});

