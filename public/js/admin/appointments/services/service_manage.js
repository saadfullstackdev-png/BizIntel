var FormValidation = function () {

    var e = function () {
        $('.select2').select2({width: '100%'});
        resetDropdowns();
    }

    var loadLocations = function (cityId) {
        if(cityId != '' && cityId != null) {
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
        Utils.updateAppointmentsQueryString(false);
    }

    let loadMachines = function (locationId) {
        Utils.ajaxGetRequest(
            route('admin.appointments.center_machines', {
                location_id: locationId
            }),
            function (response) {
                if(response.status == 1){
                    $('#machine_id').html(response.dropdown);
                    $('.select2').select2({ width: '100%' });
                    $('#machine_id').val(window.eventData.machine_id).trigger("change");
                }else{
                   resetMachines();
                }


            },
            function (xhr, ajaxOptions, thrownError) {

            });
    };

    var loadDoctors = function (locationId) {
        var service_manage = $('#appointment_manager').val();
        console.log(service_manage);
        if(locationId !== '' && locationId != null) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_doctors'),
                type: 'POST',
                data: {
                    location_id: locationId,
                    appointment_manage: service_manage,
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
            loadMachines(locationId);
        } else {
            resetDoctors();
            resetMachines();
        }
        Utils.updateAppointmentsQueryString(false);
    };


    /**
     * hide calander when no doctor and machine slected
     */
    let calenderHide = function () {
        if (
            $("#doctor_id").find(":selected").val() == undefined
            || $("#doctor_id").find(":selected").val() == ""
            || $("#machine_id").find(":selected").val() == ""
            || $("#machine_id").find(":selected").val() == undefined
        ) {
            $("#calander_block").css("display", 'none');
        }
    };

    var doctorListener = function (doctorId) {
        if(
            doctorId !== ''
            && doctorId != null
            && $("#machine_id").find(":selected").val() !==""
            && $("#machine_id").find(":selected").val() !==null
            && $("#machine_id").find(":selected").val() !==undefined
        ) {
            $("#calander_block").css("display","block");
            AppCalendar.init();
        } else {
            $('#calendar').val('');
            calenderHide();
        }
        Utils.updateAppointmentsQueryString(false);
    }

    var machineListener = function (machineId) {
        if(
            machineId !== ''
            && $("#machine_id").find(":selected").val() !==""
            && $("#machine_id").find(":selected").val() !== null
            && $("#doctor_id").find(":selected").val() !==""
            && $("#doctor_id").find(":selected").val() !== null
        ) {
            $("#calander_block").css("display","block");
            AppCalendar.init();
        } else {
            $('#calendar').val('');
            calenderHide();
        }
        Utils.updateAppointmentsQueryString(false);
    }

    var resetDropdowns = function () {
        resetLocations();
        resetDoctors();
        resetMachines();
    }

    var resetLocations = function () {
        $('.location_id').html(locationDropdown);
        $('.select2').select2({ width: '100%' });
        resetDoctors();
        resetMachines();
    }

    var resetDoctors = function () {
        $('.doctor_id').html(doctorDropdown);
        $('.select2').select2({ width: '100%' });
        calenderHide();
    }
    let resetMachines = function(){
        $('.machine_id').html(machineDropdown);
        $('#machine_id').select2({ width: '100%'});
        calenderHide();
    }

    var resetNodeServices = function () {
        $('.service_id').html(nodeServiceDropdown);
        $('.select2').select2({ width: '100%' });
    }

    var loadCalander = function () {
        urlObject = new URL(window.location.href);
        var city_id = urlObject.searchParams.get("city_id");
        var location_id = urlObject.searchParams.get("location_id");
        var doctor_id = urlObject.searchParams.get("doctor_id");
        var machine_id = urlObject.searchParams.get("machine_id");
        var id = urlObject.searchParams.get("id");
        window.eventData = {}
        window.eventData.city_id = city_id
        window.eventData.location_id = location_id
        window.eventData.doctor_id = doctor_id;
        window.eventData.machine_id = machine_id;
        window.eventData.id = id;
        window.eventData.firstTime = true;

        if (city_id) {
            $("#city_id").val(city_id).trigger("change");
        }
    };

    var locationDropdown = '<select id="location_id" class="form-control select2" name="location_id"><option value="" selected="selected">Select a Centre</option></select>';
    var doctorDropdown = '<select id="doctor_id" class="form-control select2" name="doctor_id"><option value="" selected="selected">Select a Doctor</option></select>';
    var machineDropdown = '<select id="machine_id" onchange="FormValidation.machineListener($(this).val())" class="select2" name="machine_id"><option value="" selected="selected">Select a Machine</option></select>';
    var nodeServiceDropdown = '<select id="service_id" class="form-control select2" name="service_id"><option value="" selected="selected">Select a Child Service</option></select>';

    return {
        init: function () {
            e();
            loadCalander();
        },
        loadLocations: loadLocations,
        loadDoctors: loadDoctors,
        doctorListener: doctorListener,
        machineListener: machineListener,
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({ width: '100%' });
});


/**
 * create unscheduled appointment from left panel.
 */
function addAppointment(){

    let queryParams = {};

    queryParams.city_id = $("#city_id").find(":selected").val();
    queryParams.location_id = $("#location_id").find(":selected").val();
    queryParams.doctor_id = $("#doctor_id").find(":selected").val();
    queryParams.machine_id = $("#machine_id").find(":selected").val();
    queryParams.resource_id = $("#resource_id").find(":selected").val();
    var queryString = Object.keys(queryParams).map((key) =>{
        return key + '=' + queryParams[key];
    }).join('&');


    var appointment_type = "treatment";
    base_query_string = window.location.href.split("?")[1]? "?"+window.location.href.split("?")[1] + "&" : "?";
    var edit_url = route('admin.appointments.treatment.create').url() + base_query_string + "&appointment_type=" + appointment_type;
    old_url = $("#add_treatment").attr("href");

    $("#add_treatment").attr("href", edit_url);
    $('.modal-body').html('');
    $("#add_treatment").click();
    $("#add_treatment").attr("href", old_url);




}