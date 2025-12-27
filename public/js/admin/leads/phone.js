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
                $('#patient_id').val(resposne.id)
                loadingLead();

            },
        });
    }
});


$(document).ready(function () {
    $('#phone').blur(function () {
        loadingLead();
    });
    $('#service_id').change(function () {
        loadingLead();
    });
});


function loadingLead() {
    $(".alert-danger").hide();
    var phone = $('#phone').val();
    var service = $('#service_id').val();

    var msgs = [];
    var flag = true;

    if (phone == '') {
        flag = false;
        msgs.push('Phone is required.');
    }

    if (service == '') {
        flag = false;
        msgs.push('Service is required.');
    }

    if (!flag) {
        $('#phoneTreatmentNotify').html('');
        $('#phoneTreatmentNotify').html('<div class="alert alert-danger"><button class="close" data-close="alert"></button> ' + msgs.join("<br/>") + '</div>')
        setTimeout(function () {
            $('#phoneTreatmentNotify').html('');
        }, 2000);
    } else {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: route('admin.leads.load_lead'),
            data: {
                phone: phone,
                service_id: service,
                name: $('#name').val(),
                cnic: $('#cnic').val(),
                email: $('#email').val(),
                gender: $('#gender').val(),
                dob: $('#dob').val(),
                address: $('#address').val(),
                city_id: $('#city_id').val(),
                location_id: $('#location_id').val(),
                town_id: $('#town_id').val(),
                lead_source_id: $('#lead_source_id').val(),
                lead_status_id: $('#lead_status_id').val(),
                referred_by: $('#referred_by').val(),
                id: $('#lead_id').val(),
                patient_id:$('#patient_id').val(),
            },
            success: function (response) {
                if (response.status != '1') {
                    $('#phone').val(response.phone);
                    $('#service_id').val(response.service_id);
                }
                $('#gender').val(response.gender).select2().trigger('change');
                $('#email').val(response.email);
                $('#name').val(response.name);
                $('#cnic').val(response.cnic);
                $('#dob').val(response.dob);
                $('#address').val(response.address);
                $('#city_id').val(response.city_id).select2().trigger('change');
                $('#location_id').val(response.location_id).select2().trigger('change');
                $('#town_id').val(response.town_id).select2().trigger('change');
                $('#lead_source_id').val(response.lead_source_id).select2().trigger('change');
                $('#lead_status_id').val(response.lead_status_id).select2().trigger('change');
                $('#patient_id').val(response.patient_id);
                $('.referred_by').select2('destroy');
                $('#referred_by').val(response.referred_by);
                $('#referred_by').select2().trigger('change');
                $('.referred_by').select2({width: '100%'});
            }
        });
    }
}
