var TableDatatablesAjax = function () {
    var init = function () {
        var table = $('#datatable_ajax');

        var oTable = table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.business-statuses.datatable') }}',
                data: function (d) {
                    d.business_status_name = $('input[name=business_status_name]').val();
                    d.status = $('select[name=status]').val();
                    d.action = d.customActionType ? 'filter' : '';
                },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
                }
            },
            columns: [
                {data: 'id',       orderable: false, searchable: false},
                {data: 'name'},
                {data: 'status',  orderable: false, searchable: false},
                {data: 'actions', orderable: false, searchable: false}
            ],
            order: [[1, 'asc']],
            lengthMenu: [[25, 50, 100], [25, 50, 100]],
            pageLength: 25,
            stateSave: true,
            stateDuration: 0,
            dom: "<'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
        });

        // Group actions
        $('.table-group-action-submit').on('click', function () {
            var action = $('.table-group-action-input').val();
            var ids = oTable.column(0).checkboxes.selected().toArray();

            if (!action || ids.length === 0) return;

            $.ajax({
                url: '{{ route('admin.business-statuses.datatable') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    customActionType: 'group_action',
                    customActionName: action,
                    id: ids
                },
                success: function () { oTable.ajax.reload(); }
            });
        });

        // Filter submit
        $('.filter-submit').on('click', function () {
            oTable.ajax.reload();
        });
        $('.filter-cancel').on('click', function () {
            $('input.form-filter, select.form-filter').val('');
            oTable.ajax.reload();
        });
    };

    return {init: init};
}();

jQuery(document).ready(function () {
    TableDatatablesAjax.init();
    $('.select2').select2({width: '100%'});
});