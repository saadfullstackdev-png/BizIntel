var TableDatatablesAjax = function () {
    var a = function () {
        $(".date-picker").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".created_from").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".created_to").datepicker({rtl: App.isRTL(), autoclose: !0});
        $('.select2').select2({ width: '100%' });
        // $(".select2").select2();
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
                    localStorage.setItem( 'DataTables_appointments', JSON.stringify(data) )
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse( localStorage.getItem( 'DataTables_appointments') )
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
                // lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                lengthMenu: [[25, 50, 100, 300, 600, 800], [25, 50, 100, 300, 600, 800]],
                pageLength: 25,
                "columns": [
                    { "data": "Patient_ID"},
                    { "data": "name","bSortable": false},
                    { "data": "phone","bSortable": false},
                    { "data": "scheduled_date","bSortable": true},
                    { "data": "doctor_id","bSortable": false},
                    { "data": "region_id","bSortable": false},
                    { "data": "city_id","bSortable": false},
                    { "data": "town_id","bSortable": false},
                    { "data": "location_id","bSortable": false},
                    { "data": "lead_source_id","bSortable": false},
                    { "data": "service_id","bSortable": false},
                    { "data": "appointment_status_id","bSortable": false},
                    { "data": "buisness_status_id","bSortable": false},
                    { "data": "appointment_type_id","bSortable": false},
                    { "data": "consultancy_type","bSortable": false},
                    { "data": "created_at","bSortable": true},
                    { "data": "created_by","bSortable": false},
                    { "data": "converted_by","bSortable": false},
                    { "data": "updated_by","bSortable": false},
                    { "data": "source","bSortable": false},
                    { "data": "actions","bSortable": false }
                ],
                "columnDefs": [
                    { "width": "50px", "targets": 0 },
                    { "width": "100px", "targets": 1 },
                    { "width": "100px", "targets": 2 },
                    { "width": "100px", "targets": 3 },
                    { "width": "100px", "targets": 4 },
                    { "width": "150px", "targets": 5 },
                    { "width": "100px", "targets": 6 },
                    { "width": "100px", "targets": 7 },
                    { "width": "100px", "targets": 8 },
                    { "width": "100px", "targets": 9 },
                    { "width": "100px", "targets": 10 },
                    { "width": "100px", "targets": 11 },
                    { "width": "100px", "targets": 12 },
                    { "width": "100px", "targets": 13 },
                    { "width": "100px", "targets": 14 },
                    { "width": "100px", "targets": 15 },
                    { "width": "300px", "targets": 16 },
                    { "width": "300px", "targets": 17 },
                    { "width": "300px", "targets": 18 },
                    { "width": "100px", "targets": 19 },
                    { "width": "100px", "targets": 20 },
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    "timeout": 99999999,
                    url: route('admin.appointments.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                order: [],
                "fnDrawCallback" : function(e) {
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
                    $('.select2').select2({ width: '100%' });
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
            $(".select2").select2();
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