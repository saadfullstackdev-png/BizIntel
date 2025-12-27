var TableDatatablesAjax = function () {
    var a = function () {
        $(".date-picker").datepicker({rtl: App.isRTL(), autoclose: !0})
        $('.select2').select2({ width: '100%' });
    };

    e = function () {
        var a = new Datatable;
        a.init({
            src: $("#datatable_ajax"), onSuccess: function (a, e) {
            }, onError: function (a) {
            }, onDataLoad: function (a) {
            }, loadingMessage: "Loading...", dataTable: {
                "bStateSave": true,
                stateSave: true,
                stateSaveCallback: function(settings,data) {
                    localStorage.setItem( 'DataTables_leads', JSON.stringify(data) )
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse( localStorage.getItem( 'DataTables_leads') )
                },
                fnStateSaveParams: function (a, e) {
                    return $("#datatable_ajax tr.filter .form-control").each(function () {
                        e[$(this).attr("name")] = $(this).val()
                    }), e
                },
                fnStateLoadParams: function (a, e) {
                    return $("#datatable_ajax tr.filter .form-control").each(function () {
                        var a = $(this);
                        e[a.attr("name")] && a.val(e[a.attr("name")])
                    }), !0
                },
                lengthMenu: [[25, 50, 100, 300, 600, 800,5000,10000,100000], [25, 50, 100, 300, 600, 800,5000,10000,100000]],
                pageLength: 25,
                "columns": [
                    { "data":"PatientId"},
                    {"data": "name", "bSortable": true},
                    { "data": "phone" },
                    { "data": "city_id" },
                    { "data": "region_id" },
                    { "data": "location_id" },
                    { "data": "lead_source_id" },
                    { "data": "lead_status_id" },
                    {"data": "service_id"},
                    { "data": "created_at" },
                    { "data": "created_by" },
                    { "data": "source" },
                    { "data": "actions","bSortable": false }
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    "timeout": 99999999,
                    url: route('admin.leads.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                order: [],
                "fnDrawCallback" : function(e) {
                    // Editing opitons for Lead Status
                    $('.lead_status').editable({
                        url: route('admin.leads.save_status'),
                        title: 'Change Lead Status',
                        ajaxOptions: {
                            type: 'put',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }
                    });
                    // Editing options for City
                    $('.city').editable({
                        url: route('admin.leads.save_city'),
                        title: 'Change City',
                        ajaxOptions: {
                            type: 'put',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }
                    });
                    // Editing options for Treatment
                    $('.treatment').editable({
                        url: route('admin.leads.save_treatment'),
                        title: 'Change Treatment',
                        ajaxOptions: {
                            type: 'put',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        }
                    });
                    $('[data-toggle="tooltip"]').tooltip();
                    // Initalize Clipboard Copy
                    var clipboard = new ClipboardJS('.clipboard');
                    clipboard.on('success', function(e) {
                        $('#clipboardNotify').html('');
                        $('#clipboardNotify').html('<div class="bootstrap-growl alert alert-info alert-dismissible" style="position: fixed; margin: 0px; z-index: 9999; top: 81px; width: 250px; right: 20px;"><button class="close" data-dismiss="alert" type="button"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>Phone is copied.</div>')
                        setTimeout(function() {
                            $('#clipboardNotify').html('');
                        }, 3000);

                        e.clearSelection();
                    });
                },
                buttons: [
                    {extend: "pdf", className: "btn default"},
                    {extend: "excel", className: "btn default"},
                    {extend: "csv", className: "btn default"},
                ]
            }
        }), a.getTableWrapper().on("click", ".table-group-action-submit", function (e) {
            e.preventDefault();
            var t = $(".table-group-action-input", a.getTableWrapper());
            "" != t.val() && a.getSelectedRowsCount() > 0 ? (a.setAjaxParam("customActionType", "group_action"), a.setAjaxParam("customActionName", t.val()), a.setAjaxParam("id", a.getSelectedRows()), a.getDataTable().ajax.reload(), a.clearAjaxParams()) : "" == t.val() ? App.alert({
                type: "danger",
                icon: "warning",
                message: "Please select an action",
                container: a.getTableWrapper(),
                place: "prepend"
            }) : 0 === a.getSelectedRowsCount() && App.alert({
                type: "danger",
                icon: "warning",
                message: "No record selected",
                container: a.getTableWrapper(),
                place: "prepend"
            })
        }), a.getTableWrapper().on("click", ".filter-cancel", function (e) {
            $(".select2").select2({
            width: "100%"
        });
        }), $("#datatable_ajax_tools > li > a.tool-action").on("click", function () {
            var t = $(this).attr("data-action");
            a.getDataTable().button(t).trigger()
        })
    };

    return {
        init: function () {
            a(), e();
        }
    }
}();
jQuery(document).ready(function () {
    TableDatatablesAjax.init()
});