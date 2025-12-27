$(document).ready(function () {
    
    $(".patient_id").select2({
        dropdownParent: $('#card_screen').val() === '1' ? $('#subscriptionModal') : $(document.body),
        width: '100%',
        placeholder: 'Select Patient',
        ajax: {
            url: route('admin.users.getpatient'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name + ' - ' + item.phone,
                            id: item.id
                        };
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 3,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });
    $(".patient_new_field").select2({
        width: '100%',
        dropdownParent: $('#ajax_appointments_create'),
        placeholder: 'Select Patient',
        ajax: {
            url: route('admin.users.getpatient'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name + ' - ' + item.phone,
                            id: item.id
                        };
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 3,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });
    function formatRepo(item) {
        return item.loading ? item.text : item.text;
    }

    function formatRepoSelection(item) {
        return item.id
            ? item.text + " <button onclick='addUsers()' class='croxcli' style='float: right;border: 0; background: none;padding: 0 0 0;'><i class='fa fa-times' aria-hidden='true'></i></button>"
            : 'Select Patient';
    }

    function addUsers() {
        $('.patient_id').val(null).trigger('change');
    }

    if ($('#edit_plan_screen').val()==1) {
        $('#service_id').select2();
    }
    else if($('#edit_plan_screen').val()==2){        
        $('#service_id,#consultancy_type,#referred_by,#base_service_id,#town_id').select2({
            dropdownParent: $('#ajax_leads'),
            width: '100%'
        });
    } else {
        $('#service_id,#consultancy_type,#base_service_id,#town_id').select2({
            dropdownParent: $('#ajax_appointments_create'),
            width: '100%'
        });
    }
    $('#user_location').select2({
        dropdownParent: $('#subscriptionModal'),
        width: '100%'
    });
});
