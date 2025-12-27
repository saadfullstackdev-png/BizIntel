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
                bStateSave: !1,
                cache: !0,
                stateSaveCallback: function(settings,data) {
                    localStorage.setItem( 'DataTables_patient_appointments', JSON.stringify(data) )
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse( localStorage.getItem( 'DataTables_patient_appointments') )
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
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                pageLength: 25,
                "columns": [
                    { "data": "name","bSortable": true},
                    { "data": "phone","bSortable": true},
                    { "data": "scheduled_date","bSortable": true},
                    { "data": "doctor_id","bSortable": true},
                    { "data": "city_id","bSortable": true},
                    { "data": "location_id","bSortable": true},
                    { "data": "service_id","bSortable": true},
                    { "data": "appointment_status_id","bSortable": true},
                    { "data": "appointment_type_id","bSortable": true},
                    { "data": "consultancy_type","bSortable": true},
                    { "data": "created_at","bSortable": true},
                    { "data": "created_by","bSortable": true},
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
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    url: route('admin.patients.appointmentsDatatable',[$('#patient_id').val()]),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: !0,
                order: [[9, "desc"]],
                "fnDrawCallback" : function(e) {
                },
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