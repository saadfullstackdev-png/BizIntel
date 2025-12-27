$(document).on('change', '#parent_id_1', function () {
    var patient_id = $('#parent_id_1').val();
    if (patient_id) {
        $.ajax({
            type: 'get',
            url: route('admin.users.get_patient_number'),
            data: {
                'patient_id': patient_id
            },
            success: function (resposne) {
                $('#phone').val(resposne.phone);
                $('#patient_id').val(resposne.id);

                CreateFormValidation.loadLead();
            },
        });
    }
});

$(document).on('change', '#service_id', function () {
    var service_id = $('#service_id').val();
    if (service_id) {
        $.ajax({
            type: 'get',
            url: route('admin.appointments.checkconsultancytype'),
            data: {
                'service_id': service_id
            },
            success: function (resposne) {
                $(".consultancy_type").empty();
                if (resposne.consultancy_type === 'in_person') {
                    var in_person = new Option('In Person', 'in_person', false, true);
                    $('.consultancy_type').append(in_person).trigger('change');

                } else if (resposne.consultancy_type === 'virtual') {
                    var virtual = new Option('Virtual', 'virtual', false, true);
                    $('.consultancy_type').append(virtual).trigger('change');

                } else {
                    var Option_0 = new Option('Select Consultancy Type', '', false, true);
                    var Option_1 = new Option('In Person', 'in_person', false, false);
                    var Option_2 = new Option('Virtual', 'virtual', false, false);
                    $('.consultancy_type').append(Option_0, Option_1, Option_2).trigger('change');
                }
            },
        });
    }
});

var CreateFormValidation = function () {

    var e = function () {
        var e = $("#create-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
        e.validate({
            errorElement: "span",
            errorClass: "help-block help-block-error",
            focusInvalid: !1,
            ignore: "",
            messages: {},
            rules: {
                city_id: {required: !0},
                location_id: {required: !0},
                doctor_id: {required: !0},
                service_id: {required: !0},
                // lead_source_id: {required: '#lead_source_id:visible'},
                name: {required: !0},
                phone: {required: !0, number: true},
                // dob: {required: !0},
                // address: {required: !0},
                email: {email: true},
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
                $("input[type=submit]", e).attr('disabled', e);

                x(e.attr('action'), e.attr('method'), e.serialize(), function (response) {
                    if (response.status == '1') {

                        r.hide();
                        i.html(response.message);
                        // resetForm();
                        $('#ajax_appointments_create').modal('toggle');

                        /*
                         * Set created event id and re-init Calendar
                         */
                        AppCalendar.setEventId(response.id);
                        AppCalendar.init();
                        // window.eventData.createdId = response.id;
                        $("input[type=submit]", e).removeAttr('disabled');
                    } else {
                        $("input[type=submit]", e).removeAttr('disabled');
                        i.hide();
                        r.html(response.message);
                        r.show();
                    }
                });
                return false;
            }
        });

        $('.select2').select2({width: '100%'});
        $('#referred_by').select2({width: '100%'});

        $('#phone').blur(function () {
            loadLead();
        });
        $('#service_id').change(function () {
            loadLead();
        });
        $('.dob').datepicker({
            format: 'yyyy-mm-dd',
        }).on('changeDate', function (ev) {
            $(this).datepicker('hide');
        });
        $("#cnic").inputmask("99999-9999999-9", {
            placeholder: "XXXXX-XXXXXXX-X",
            clearMaskOnLostFocus: true
        });
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
                        'id': response.id,
                    });
                } else {
                    callback({
                        'status': response.status,
                        'message': response.message.join('<br/>'),
                        'id': null
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (xhr.status == '401') {
                    callback({
                        'status': 0,
                        'message': 'You are not authorized to access this resouce',
                        'id': null
                    });
                } else {
                    callback({
                        'status': 0,
                        'message': 'Unable to process your request, please try again later.',
                        'id': null
                    });
                }
            }
        });
    }

    var resetForm = function () {
        $('#phone').val('');
        $('#name').val('');
        $('#email').val('');
        $('#dob').val('');
        $('#address').val('');
        $('#cnic').val('');
        $('#referred_by').select2('destroy');
        $('#referred_by').val('');
        $('#service_id').val('').trigger('change');
        $('#lead_source_id').val('');
        $('#lead_source_id').hide();
    }

    var loadLead = function () {

        var phone = $('#phone').val();
        var lead_id = $('#lead_id').val();
        var patient_id = $('#patient_id').val();
        var service_id = $('#service_id').val();
        var dob = $('#dob').val();
        var address = $('#address').val();
        var town_id = $('#town_id').val();
        var cnic = $('#cnic').val();
        var gender = $('#gender').val();
        var referred_by = $('#referred_by').val();

        var flag = true;

        if (!phone) {
            $('#phone').valid();
            flag = false;
        }
        if (!service_id) {
            $('#service_id').valid();
            flag = false;
        }

        if (!flag) {
            return false;
        } else {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: route('admin.appointments.load_lead'),
                data: {
                    phone: phone,
                    dob: dob,
                    address: address,
                    town_id: town_id,
                    cnic: cnic,
                    referred_by: referred_by,
                    service_id: service_id,
                    lead_id: lead_id,
                    patient_id: patient_id,
                    gender: gender,

                },
                success: function (response) {
                    $('#email').show();
                    $('#lead_source_id').show();
                    $('#phone').val(response.phone);
                    $('#email').val(response.email);
                    $('#name').val(response.name);
                    $('#service_id').val(response.service_id);
                    $('#lead_source_id').val(response.lead_source_id);
                    $('#lead_id').val(response.lead_id);
                    $('#patient_id').val(response.patient_id);
                    $('#dob').val(response.dob);
                    $('#address').val(response.address);
                    $('#town_id').val(response.town_id).change();
                    $('#cnic').val(response.cnic);
                    $('#gender').val(response.gender);
                    $('#referred_by').select2('destroy');
                    $('#referred_by').val(response.referred_by);
                    $('#referred_by').select2({width: '100%'});
                }
            });
        }
    }

    return {
        init: function () {
            e();
        },
        loadLead: loadLead
    }
}();
jQuery(document).ready(function () {
    CreateFormValidation.init();
});