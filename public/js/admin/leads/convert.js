var FormValidation = function () {
    var e = function () {
        console.log(route('admin.appointments.create', {city_id: 1, location_id: 3, doctor_id: 5}));

        var e = $("#convert-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {
            },
            rules: {
                city_id: {required: !0},
                location_id: {required: !0},
                doctor_id: {required: !0},
                consultancy_type: {required: !0},
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
                $("input[type=submit]", e).attr('disabled', true);

                x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
                    if (response.status == '1') {
                        r.hide();
                        i.html(response.message);
                        window.location = route('admin.appointments.create', {city_id: $("#city_id").val(), location_id: $('#location_id').val(), doctor_id: $('#doctor_id').val()});
                    } else {
                        $("input[type=submit]", e).removeAttr('disabled');
                        i.hide();
                        r.html(response.message);
                        r.show();
                    }
                });
                return false;
            }
        })
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
            success: function (response) {
                if (response.status == '1') {
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
                if (xhr.status == '401') {
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
    }

    /**
     * Update All query string param of city_id, location_id. doctor_id
     */
    var loadDoctors = function (locationId) {
        if (locationId != '' && locationId != null) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_doctors'),
                type: 'POST',
                data: {
                    location_id: locationId
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.doctor_id').html(response.dropdown);
                        $('.select2').select2({ width: '100%' });
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
    }

    let doctorListener = function (doctorId) {

    }

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
    }

    var locationDropdown = '<select id="location_id" class="form-control select2 required" name="location_id"><option value="" selected="selected">Select a Centre</option></select>';
    var doctorDropdown = '<select id="doctor_id" class="form-control select2 required" name="doctor_id"><option value="" selected="selected">Select a Doctor</option></select>';

    return {
        init: function () {
            e()
        },
        loadLocations: loadLocations,
        loadDoctors: loadDoctors,
        doctorListener: doctorListener
    }
}();
jQuery(document).ready(function () {
    FormValidation.init();
    $('.select2').select2({ width: '100%' });
});