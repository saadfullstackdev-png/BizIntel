$(document).ready(function () {
    $(".package_selling_id").select2({
        width: '100%',
        placeholder: 'Select Package Selling',
        ajax: {
            url: route('admin.packages.getpackageselling'),
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
                            text: item.id,
                            id: item.id
                        }
                    }),
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 1,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    function formatRepo(item) {
        if (item.loading) {
            return item.text;
        }
        markup = item.text;
        return markup;
    }

    function formatRepoSelection(item) {
        if (item.id) {
            return item.text + " <button onclick='addUsers()' class='croxcli' style='float: right;border: 0; background: none;padding: 0 0 0;'><i class='fa fa-times' aria-hidden='true'></i></button>";
        } else {
            return 'Select Package Selling';
        }
    }
});

function addUsers() {
    $('.package_selling_id').val(null).trigger('change');
}