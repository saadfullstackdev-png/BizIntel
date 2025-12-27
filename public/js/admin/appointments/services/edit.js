var EditServiceFormValidation = function () {
    var e = function () {
        var e = $("#edit-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
            },
            rules: {
                name: {required: !0},
                city_id: {required: !0},
                location_id: {required: !0},
                doctor_id: {required: !0},
                service_id: {required: !0},
                scheduled_date: {required: !0},
                scheduled_time: {required: !0},
                mobile: {required: !0},
            },
            invalidHandler: function (e, t) {
                i.hide(), r.show(), App.scrollTo(r, -200)
            },
            errorPlacement: function (e, r) {
                var i = $(r).parent(".input-group");
                i.size() > 0 ? i.after(e) : r.after(e)
            },
            highlight: function (e) {
                $(e).closest(".form-group").addClass("has-error")
            },
            unhighlight: function (e) {
                $(e).closest(".form-group").removeClass("has-error")
            },
            success: function (e) {
                e.closest(".form-group").removeClass("has-error")
            },
            submitHandler: function (event) {
                i.show(), r.hide();
                $("input[type=submit]",e).attr('disabled', true);

                x(e.attr('action'),e.attr('method'), e.serialize(), function (response) {
                    if(response.status == '1') {
                        r.hide();
                        i.html(response.message);

                        if(typeof $('#backurl').val() == 'undefined') {
                            window.location = route('admin.appointments.index');
                        } else {
                            [base_url, query_string] = $('#backurl').val().split("?");
                            urlParams = new URLSearchParams(query_string)
                            urlParams.delete('id');
                            full_url = base_url+ "?"+ urlParams.toLocaleString();
                            window.location = full_url;
                        }
                    } else {
                        $("input[type=submit]",e).removeAttr('disabled');
                        i.hide();
                        r.html(response.message);
                        r.show();
                    }
                });
                return false;
            }
        });
        $('.form-control.inpt-focus').focus();
    }

    var x = function (action, method, data, callback) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: action,
            type: method,
            data: data,
            cache: false,
            success: function(response) {
                 if(response.status == '1') {
                    callback({
                        'status': response.status,
                        'message': response.message,
                    });
                } else {
                    callback({
                        'status': response.status,
                        'message': response.message.join('<br/>'),
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if(xhr.status == '401') {
                    callback({
                        'status': 0,
                        'message': 'You are not authorized to access this resouce',
                    });
                } else {
                    callback({
                        'status': 0,
                        'message': 'Unable to process your request, please try again later.',
                    });
                }
            }
        });
    }

    var loadLocations = function (cityId) {
        var service_manage = $('#appointment_manager').val();
        if(cityId != '') {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_locations'),
                type: 'POST',
                data: {
                    city_id: cityId,
                    form: 'EditServiceFormValidation',
                    idPrefix: 'service_',
                    service_id: $('#service_service_id').val(),
                    appointment_manage: service_manage,
                    machine_type_allocation: 'allowed'
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.service_location_id').html(response.dropdown);
                        $('#service_location_id').select2({ width: '100%' });
                        resetDoctors();
                        resetScheduledDate();
                        resetScheduledTime();
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
    }

    /**
     * Update All query string param of city_id, location_id. doctor_id
     */
    var loadDoctors = function (locationId) {
        var service_manage = $('#appointment_manager').val();
        if (locationId != '' && locationId != null) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_doctors'),
                type: 'POST',
                data: {
                    location_id: locationId,
                    form: 'EditServiceFormValidation',
                    idPrefix: 'service_',
                    service_id: $('#service_service_id').val(),
                    appointment_manage: service_manage,
                    machine_type_allocation: 'allowed'
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.service_doctor_id').html(response.dropdown);
                        $('#service_doctor_id').select2({ width: '100%' });
                    } else {
                        resetDoctors();
                        resetScheduledDate();
                        resetScheduledTime();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    resetDoctors();
                    resetScheduledDate();
                    resetScheduledTime();
                }
            });
            loadMachines(locationId);
        } else {
            resetDoctors();
            resetScheduledDate();
            resetScheduledTime();
        }
    }

    var loadMachines = function (locationId) {
        var service_manage = $('#appointment_manager').val();
        Utils.ajaxGetRequest(
            route('admin.appointments.center_machines', {
                location_id: locationId,
                form: 'EditServiceFormValidation',
                idPrefix: 'service_',
                service_id: $('#service_service_id').val(),
                appointment_manage: service_manage,
                machine_type_allocation: 'allowed'

            }),
            function (response) {
                if(response.status == 1){
                    $('.service_machine_id').html(response.dropdown);
                    $('#service_machine_id').select2({ width: '100%' });
                }else{
                    resetMachines();
                }
            },
            function (xhr, ajaxOptions, thrownError) {
                resetMachines();
            });
    };

    let doctorListener = function (doctorId) {
        var machineId = $("#service_machine_id").val();
        listenerDoctorMachine(doctorId, machineId);
    }

    var machineListener = function (machineId) {
        var doctorId = $("#service_doctor_id").val();
        listenerDoctorMachine(doctorId, machineId);
    }

    var listenerDoctorMachine = function(doctorId, machineId) {
        $('#rotaError').hide();
        var scheduled_date = $('#scheduled_date').val();

        if (
            (doctorId != '' && doctorId != null) &&
            (machineId != '' && machineId != null) &&
            (scheduled_date != '' && scheduled_date != null)
        ) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_doctor_rota'),
                type: 'POST',
                data: {
                    location_id: $('#service_location_id').val(),
                    doctor_id: doctorId,
                    machine_id: machineId,
                    scheduled_date: scheduled_date,
                    appointment_id: $('#appointment_id').val(),
                    resourceRotaDayID: $('#resourceRotaDayID').val(),
                    machineRotaDayID: $('#machineRotaDayID').val(),
                    form: 'EditServiceFormValidation',
                    idPrefix: 'service_'
                },
                cache: false,
                success: function(response) {
                    console.log(response);
                    if(response.status == '1') {
                        if(
                            (response.resource_has_rota_day.start_time != '' && response.resource_has_rota_day.start_time != null) &&
                            (response.resource_has_rota_day.end_time != '' && response.resource_has_rota_day.end_time != null)
                        ) {
                            resetScheduledTime();
                            if(response.resource_has_rota_day.start_off){

                                $('#scheduled_time').val(response.selected);
                                loadScheduledTime(response.resource_has_rota_day.start_time, response.resource_has_rota_day.end_time,response.resource_has_rota_day.start_off,response.resource_has_rota_day.end_off);
                            } else {
                                $('#scheduled_time').val(response.selected);
                                loadScheduledTime(response.resource_has_rota_day.start_time, response.resource_has_rota_day.end_time,false,false);
                            }

                        } else {
                            $('#rotaError').show();
                            resetScheduledTime();
                        }
                    } else {
                        resetScheduledTime();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    resetScheduledTime();
                }
            });
        } else {
            resetScheduledTime();
        }
    }

    let resetDropdowns = function () {
        resetLocations();
        resetDoctors();
        resetMachines();
        resetScheduledDate();
        resetScheduledTime();
    }

    var resetLocations = function () {
        $('.service_location_id').html(locationDropdown);
        $('#service_location_id').select2({ width: '100%' });
    }

    var resetDoctors = function () {
        $('.service_doctor_id').html(doctorDropdown);
        $('#service_doctor_id').select2({ width: '100%' });
    }

    var resetMachines = function(){
        $('.service_machine_id').html(machineDropdown);
        $('#service_machine_id').select2({ width: '100%'});
    }

    var resetScheduledDate = function () {
        $('.scheduled_date').html(scheduledDateContent);
        loadScheduledDate();
    }

    var resetScheduledTime = function () {
        $('.scheduled_time').html(scheduledTimeContent);
        loadScheduledTime();
    }

    var loadScheduledDate = function () {
        var back_date = $('#back-date').val();
        if ( back_date == 0 ) {
            var date = new Date();
            date.setDate(date.getDate());
            $('#scheduled_date').datepicker({
                format: 'yyyy-mm-dd',
                startDate: date
            }).on('changeDate', function(ev){
                $(this).datepicker('hide');
                doctorListener($('#service_doctor_id').val());
            });
        } else {
            var date = new Date();
            date.setDate(date.getDate());
            $('#scheduled_date').datepicker({
                format: 'yyyy-mm-dd',
            }).on('changeDate', function(ev){
                $(this).datepicker('hide');
                doctorListener($('#service_doctor_id').val());
            });
        }
    }

    var loadScheduledTime = function (minTime, maxTime,start_off,end_off) {
        if(minTime && start_off) {
            $('#scheduled_time').timepicker({
                'disableTimeRanges': [[start_off, end_off]],
                'useSelect': true,
                'className': 'form-control',
                'minTime': minTime,
                'maxTime': maxTime,
                'scrollbar': true,
                'step': 5,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        } else if(minTime && !start_off){
            $('#scheduled_time').timepicker({
                'useSelect': true,
                'className': 'form-control',
                'minTime': minTime,
                'maxTime': maxTime,
                'scrollbar': true,
                'step': 5,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        }
    }

    var resetScheduledTime = function () {
        $('.scheduled_time').html(scheduledTimeContent);
    }

    var locationDropdown = '<select id="service_location_id" class="form-control required" name="location_id"><option value="" selected="selected">Select a Centre</option></select>';
    var doctorDropdown = '<select id="service_doctor_id" class="form-control required" name="doctor_id"><option value="" selected="selected">Select a Doctor</option></select>';
    var machineDropdown = '<select id="service_machine_id" class="form-control required" name="machine_id"><option value="" selected="selected">Select a Machine</option></select>';
    var scheduledDateContent = '<input id="scheduled_date" readonly="true" name="scheduled_date" class="form-control" type="text" class="required" value="' + $('#scheduled_date_old').val() + '" placeholder="Schedule Date^">';
    var scheduledTimeContent = '<input id="scheduled_time" readonly="true" name="scheduled_time" class="form-control" type="text" class="required" placeholder="Schedule Time">';

    return {
        init: function () {
            e(); resetScheduledDate();
            $('#service_city_id').select2({ width: '100%' });
            $('#service_location_id').select2({ width: '100%' });
            $('#service_doctor_id').select2({ width: '100%' });
            $('#service_machine_id').select2({ width: '100%' });
        },
        loadLocations: loadLocations,
        loadDoctors: loadDoctors,
        loadMachines: loadMachines,
        doctorListener: doctorListener,
        machineListener: machineListener,
        loadScheduledDate: loadScheduledDate,
        loadScheduledTime: loadScheduledTime,
    }
}();
jQuery(document).ready(function () {
    EditServiceFormValidation.init()
    $('.custom_alert_close').on('click', function(){
        $('.custom_alert').hide();
    });
});