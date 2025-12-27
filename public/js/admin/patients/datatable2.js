var TableDatatablesCard = (function () {
    var initializeDatePickers = function () {
        var datePickerOptions = { rtl: App.isRTL(), autoclose: true };
        $(".date-picker").datepicker(datePickerOptions);
        $(".created_from").datepicker(datePickerOptions);
        $(".created_to").datepicker(datePickerOptions);
    };

    var initializeDatatable = function () {
        var datatable = new Datatable();

        datatable.init({
            src: $("#datatable_ajax"),
            onSuccess: function () {},
            onError: function () {},
            onDataLoad: function () {},
            loadingMessage: "Loading...",
            dataTable: {
                bStateSave: true,
                cache: true,
                stateSaveCallback: function (settings, data) {
                    localStorage.setItem('DataTables_cards', JSON.stringify(data));
                },
                stateLoadCallback: function () {
                    return JSON.parse(localStorage.getItem('DataTables_cards'));
                },
                fnStateSaveParams: function (settings, data) {
                    $("#datatable_ajax tr.filter .form-control").each(function () {
                        data[$(this).attr("name")] = $(this).val();
                    });
                    return data;
                },
                fnStateLoadParams: function (settings, data) {
                    $("#datatable_ajax tr.filter .form-control").each(function () {
                        var input = $(this);
                        if (data[input.attr("name")]) {
                            input.val(data[input.attr("name")]);
                        }
                    });
                    return true;
                },
                lengthMenu: [[25, 50, 100], [25, 50, 100]],
                pageLength: 25,
                columns: [
                    { data: "id", bSortable: false },
                    { data: "card_number", bSortable: false },
                    { data: "patient_id", bSortable: false },
                    { data: "time_limit", bSortable: false },
                    { data: "start_date", bSortable: false },
                    { data: "end_date", bSortable: true },
                    { data: "active", bSortable: false },
                ],
                ajax: {
                    url: route('admin.cards.datatable'),
                    beforeSend: function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    },
                },
                ordering: true,
                order: [[5, "desc"]],
            },
        });

        // Handle group action submissions
        datatable.getTableWrapper().on("click", ".table-group-action-submit", function (event) {
            event.preventDefault();
            var actionInput = $(".table-group-action-input", datatable.getTableWrapper());

            if (actionInput.val() && datatable.getSelectedRowsCount() > 0) {
                datatable.setAjaxParam("customActionType", "group_action");
                datatable.setAjaxParam("customActionName", actionInput.val());
                datatable.setAjaxParam("id", datatable.getSelectedRows());
                datatable.getDataTable().ajax.reload();
                datatable.clearAjaxParams();
            } else if (!actionInput.val()) {
                App.alert({
                    type: "danger",
                    icon: "warning",
                    message: "Please select an action",
                    container: datatable.getTableWrapper(),
                    place: "prepend",
                });
            } else if (datatable.getSelectedRowsCount() === 0) {
                App.alert({
                    type: "danger",
                    icon: "warning",
                    message: "No record selected",
                    container: datatable.getTableWrapper(),
                    place: "prepend",
                });
            }
        });
    };

    return {
        init: function () {
            initializeDatePickers();
            initializeDatatable();
        },
    };
})();

jQuery(document).ready(function () {
    TableDatatablesCard.init();
});
