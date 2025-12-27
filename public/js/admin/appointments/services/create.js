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

var CreateFormValidation = function () {
    var e = function () {
        var e = $("#create-validation"), r = $(".alert-danger", e), i = $(".alert-success", e);
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
                service_id: {required: !0},
                /*lead_source_id: {required: '#lead_source_id:visible'},*/
                name: {required: !0},
                phone: {required: !0, number: true},
               /* dob: {required: !0},*/
               /* address: {required: !0},*/
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
                $("input[type=submit]",e).attr('disabled', e);

                x(e.attr('action'),e.attr('method'), e.serialize(), function (response) {
                    if(response.status == '1') {
                        r.hide();
                        i.html(response.message);
                        // resetForm();
                        $('#ajax_appointments_create').modal('toggle');

                        /*
                         * Set created event id and re-init Calendar
                         */
                        AppCalendar.setEventId(response.id);
                        AppCalendar.init();

                        $("input[type=submit]",e).removeAttr('disabled');
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

        $('#phone').blur(function () {
            CreateFormValidation.loadLead();
        });
        $('#service_id').change(function () {
            loadEndServices();
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

        $('.select2').select2({ width: '100%' });
    }

    var resetForm = function () {
        $('#phone').val('');
        $('#name').val('');
        $('#email').val('');
        $('#dob').val('');
        $('#address').val('');
        $('#cnic').val('');
        $('#referred_by').val('');
        $('#service_id').val('').trigger('change');
        $('#lead_source_id').val('');
        $('#lead_source_id').hide();
        resetNodeServices();
    }

    var x = function(action, method, data, callback) {
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

    var loadLead = function() {
        var phone = $('#phone').val();
        var lead_id = $('#lead_id').val();
        var patient_id = $('#patient_id').val();
        var service_id = $('#base_service_id').val();
        var dob = $('#dob').val();
        var address = $('#address').val();
        var town_id = $('#town_id').val();
        var cnic = $('#cnic').val();
        var referred_by = $('#referred_by').val();
        var gender = $('#gender').val();


        var flag = true;

        if(!phone) {
            $('#phone').valid();
            flag = false;
        }
        if(!service_id) {
            $('#service_id').valid();
            flag = false;
        }

        if(!flag) {
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
                    service_id: service_id,
                    dob: dob,
                    address: address,
                    town_id: town_id,
                    cnic: cnic,
                    referred_by: referred_by,
                    lead_id: lead_id,
                    patient_id:patient_id,
                    gender:gender
                },
                success: function(response){
                    $('#email').show();
                    $('#lead_source_id').show();
                    $('#phone').val(response.phone);
                    $('#email').val(response.email);
                    $('#name').val(response.name);
                    $('#base_service_id').val(response.service_id);
                    $('#lead_source_id').val(response.lead_source_id);
                    $('#lead_id').val(response.lead_id);
                    $('#patient_id').val(response.patient_id);
                    $('#dob').val(response.dob);
                    $('#address').val(response.address);
                    $('#town_id').val(response.town_id).change();
                    $('#cnic').val(response.cnic);
                    $('#gender').val(response.gender);
                    $('#referred_by').val(response.referred_by);
                }
            });
        }
    }

    var loadEndServices = function (baseServiceId) {
        if(baseServiceId != '') {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: route('admin.appointments.load_node_service'),
                type: 'POST',
                data: {
                    service_id: baseServiceId
                },
                cache: false,
                success: function(response) {
                    if(response.status == '1') {
                        $('.service_id').html(response.dropdown);
                        $('#service_id').select2({ width: '100%' });
                    } else {
                        resetNodeServices();
                    }
                    CreateFormValidation.loadLead();
                },
                error: function (xhr, ajaxOptions, thrownError) {

                }
            });
        } else {
            resetNodeServices();
            CreateFormValidation.loadLead();
        }
    }

    var resetNodeServices = function () {
        $('.service_id').html(nodeServiceDropdown);
        $('#service_id').select2({ width: '100%' });
    }

    var nodeServiceDropdown = '<select id="service_id" class="form-control select2" name="service_id"><option value="" selected="selected">Select a Child Service</option></select>';

    return {
        init: function () {
            e();
        },
        loadEndServices: loadEndServices,
        loadLead: loadLead,
    }
}();
jQuery(document).ready(function () {
    CreateFormValidation.init();
});
