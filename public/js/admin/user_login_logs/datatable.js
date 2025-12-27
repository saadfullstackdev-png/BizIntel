var TableDatatablesAjax = function () {
    var a = function () {
        $(".date-picker").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".created_from").datepicker({rtl: App.isRTL(), autoclose: !0});
        $(".created_to").datepicker({rtl: App.isRTL(), autoclose: !0});
        // $('.select2').select2({ width: '100%' });
    };

    e = function () {
        var a = new Datatable;
        a.init({
            src: $("#datatable_ajax"), onSuccess: function (a, e) {
            }, onError: function (a) {
            }, onDataLoad: function (a) {
            }, loadingMessage: "Loading...", dataTable: {
                bStateSave: !0,
                cache: !0,
                stateSaveCallback: function(settings,data) {
                    localStorage.setItem( 'DataTables_users', JSON.stringify(data) )
                },
                stateLoadCallback: function(settings) {
                    return JSON.parse( localStorage.getItem( 'DataTables_users') )
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
                lengthMenu: [[25, 50, 100,-1], [25, 50, 100,'All']],
                pageLength: 25,
                "columns": [
                    {"data": "id", "bSortable": false},
                    {"data": "user_id","bSortable": false},
                    {"data": "user_email","bSortable": false},
                    {"data": "user_ip","bSortable": false},
                    {"data": "location","bSortable": false},
                    {"data": "machine_name","bSortable": false},
                    {"data": "browser","bSortable": false},
                    {"data": "os","bSortable": false},
                    {"data": "longitude","bSortable": false},
                    {"data": "latitude","bSortable": false},
                    {"data": "country","bSortable": false},
                    {"data": "country_code","bSortable": false},
                    {"data": "login_time","bSortable": false},
                    {"data": "logout_time","bSortable": false},
                    {"data": "created_at","bSortable": true},
                    { "data": "actions","bSortable": false }
                ],
                ajax: {
                    // url: "../demo/table_ajax.php",
                    url: route('admin.user_login_logs.datatable'),
                    'beforeSend': function (request) {
                        request.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                ordering: true,
                order: [[8, "desc"]],
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
        }),$("#datatable_ajax_tools > li > a.tool-action").on("click", function () {
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