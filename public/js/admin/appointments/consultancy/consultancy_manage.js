
var FormValidation = function () {
    var e = function () {
        $('.select2').select2({dropdownParent: $('#ajax_appointments_create'),width: '100%'});
        resetDropdowns();
    }

    var loadLocations = function (cityId) {
        $("#add_consulting").attr('href',route('admin.appointments.consulting.create')+ "?" + window.location.href.split("?")[1]);

        if(cityId != '') {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_locations'),
                type: 'POST',
                data: {
                    city_id: cityId
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.location_id').html(response.dropdown);
                        $('.select2').select2({ width: '100%' });
                        $('#location_id').val(window.eventData.location_id).trigger("change");
                        if ($("#location_id").find(":selected").val() == undefined || $("#location_id").find(":selected").val() == "") {

                            window.eventData = {};
                            $('#location_id').val('').select2();
                        }
                        resetDoctors();
                    } else {
                        resetDropdowns();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    resetDropdowns();
                }
            });
        } else {
            resetDropdowns();
        }
        Utils.updateAppointmentsQueryString();
    }

    /**
     * Update All query string param of city_id, location_id. doctor_id
     */
    var loadDoctors = function (locationId) {
        $("#add_consulting").attr('href',route('admin.appointments.consulting.create')+ "?" + window.location.href.split("?")[1]);
        var consultancy_manager = $('#appointment_manager').val();
        if (locationId != '' && locationId != null) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_doctors'),
                type: 'POST',
                data: {
                    location_id: locationId,
                    appointment_manage: consultancy_manager,
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.doctor_id').html(response.dropdown);
                        $('.select2').select2({ width: '100%' });
                        $('#doctor_id').val(window.eventData.doctor_id).trigger("change");
                    } else {
                        resetDoctors();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    resetDoctors();
                }
            });
        } else {
            resetDoctors();
        }
        Utils.updateAppointmentsQueryString();
    }

    let doctorListener = function (doctorId) {
        if (doctorId != '' && doctorId != null) {
            $("#add_consulting").attr('href',route('admin.appointments.consulting.create')+ "?" + window.location.href.split("?")[1]);
            $("#calander_block").css("display",'block');
            AppCalendar.init();
        } else {
            calenderHide();
            $('#calendar').val('');
        }
        Utils.updateAppointmentsQueryString();
    }

    let calenderHide = function () {
        if ($("#doctor_id").find(":selected").val() == undefined || $("#doctor_id").find(":selected").val() == "") {
            $("#calander_block").css("display", 'none');
        }
    };
    
    let resetDropdowns = function () {
        resetLocations();
        resetDoctors();
    }

    var resetLocations = function () {
        $('.location_id').html(locationDropdown);
        $('.select2').select2({ width: '100%' });
    }

    var resetDoctors = function () {
        $('.doctor_id').html(doctorDropdown);
        $('.select2').select2({ width: '100%' });
        calenderHide();
    }

    var resetServices = function () {
        $('.service_id').html(serviceDropdown);
       $('.select2').select2({ width: '100%' });
    }

    var loadCalendar = function () {
        urlObject = new URL(window.location.href);
        var city_id = urlObject.searchParams.get("city_id");
        var location_id = urlObject.searchParams.get("location_id");
        var doctor_id = urlObject.searchParams.get("doctor_id");
        var id = urlObject.searchParams.get("id");
        window.eventData = {}
        window.eventData.city_id = city_id
        window.eventData.location_id = location_id
        window.eventData.doctor_id = doctor_id;
        window.eventData.id = id;
        window.eventData.firstTime = true;
        if (city_id) {
            $("#city_id").val(city_id).trigger("change");
        }
    };
    var locationDropdown = '<select id="location_id" class="form-control select2 required" name="location_id"><option value="" selected="selected">Select a Centre</option></select>';
    var doctorDropdown = '<select id="doctor_id" class="form-control select2 required" name="doctor_id"><option value="" selected="selected">Select a Doctor</option></select>';
    var serviceDropdown = '<select id="service_id" class="form-control select2 required" name="service_id"><option value="" selected="selected">Select a Service</option></select>';

    return {
        init: function () {
            e();
            loadCalendar()
        },
        loadLocations: loadLocations,
        loadDoctors: loadDoctors,
        doctorListener: doctorListener,
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({ dropdownParent: $('#ajax_appointments_create'), width: '100%' });
    // $('.double-scroll').doubleScroll();
});

function addConsultingAppointment() {
    let queryParams = {};

    queryParams.city_id = $("#city_id").find(":selected").val();
    queryParams.location_id = $("#location_id").find(":selected").val();
    queryParams.doctor_id = $("#doctor_id").find(":selected").val();
    var queryString = Object.keys(queryParams).map((key) => {
        return key + '=' + queryParams[key];
    }).join('&');

    var appointment_type = "consulting";
    base_query_string = window.location.href.split("?")[1] ? "?" + window.location.href.split("?")[1] + "&" : "?";
    var edit_url = route('admin.appointments.consulting.create').url() + base_query_string + "&appointment_type=" + appointment_type;
    old_url = $("#add_consulting").attr("href");
    $("#add_consulting").attr("href", edit_url);
    $("#add_consulting").click();
    $("#add_consulting").attr("href", old_url);


}