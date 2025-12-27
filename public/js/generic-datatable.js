/**
 * Generic DataTable Configuration
 * A reusable DataTable setup for all modules
 */

class GenericDataTable {
    constructor(options = {}) {
        this.options = this.mergeDefaults(options);
        this.init();
    }

    mergeDefaults(options) {
        const defaults = {
            // Required options
            tableId: '#dataTable',
            ajaxUrl: null,
            columns: [],

            // Optional customizations
            title: 'Data',
            createRoute: null,
            showCreateButton: true,
            showExportButtons: true,
            showActions: true,
            actionsColumnIndex: -1,

            // DataTable settings
            order: [[1, 'asc']],
            pageLength: [10, 25, 50, 100],
            searchPlaceholder: '',

            // Custom renderers
            actionRenderer: null,
            exportFormatter: null,

            // Responsive settings
            responsiveModal: true,
            modalHeaderField: 'name',

            // Additional DataTable options
            additionalOptions: {
                processing: true,
                serverSide: true,
                stateSave: true
            }
        };

        return { ...defaults, ...options };
    }

    init() {
        if (!this.options.ajaxUrl) {
            throw new Error('ajaxUrl is required for GenericDataTable');
        }

        if (!this.options.columns.length) {
            throw new Error('columns array is required for GenericDataTable');
        }

        this.createTable();
    }

    createTableTitle() {
        const tableTitle = document.createElement('h5');
        tableTitle.classList.add('card-title', 'mb-0', 'text-md-start', 'text-center', 'pb-md-0', 'pb-6');
        tableTitle.innerHTML = this.options.title;
        return tableTitle;
    }

    getDefaultActionRenderer() {
        return function (data, type, full, meta) {
            return `
                <div class="d-inline-block">
                    <a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end m-0">
                        <!-- <li><a href="javascript:;" class="dropdown-item view-record" data-id="${full.id}">View</a></li> -->
                        <li><a href="javascript:;" class="dropdown-item edit-record" data-id="${full.id}">Edit</a></li>
                        <div class="dropdown-divider"></div>
                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record" data-id="${full.id}">Delete</a></li>
                    </ul>
                </div>
            `;
        };
    }

    getDefaultExportFormatter() {
        return function (inner, coldex, rowdex) {
            if (inner.length <= 0) return inner;
            const parser = new DOMParser();
            const doc = parser.parseFromString(inner, 'text/html');
            let text = '';

            // Handle specific elements
            const userNameElements = doc.querySelectorAll('.user-name');
            if (userNameElements.length > 0) {
                userNameElements.forEach(el => {
                    const nameText =
                        el.querySelector('.fw-medium')?.textContent ||
                        el.querySelector('.d-block')?.textContent ||
                        el.textContent;
                    text += nameText.trim() + ' ';
                });
            } else {
                text = doc.body.textContent || doc.body.innerText;
            }

            return text.trim();
        };
    }

    getExportButtons() {
        const formatter = this.options.exportFormatter || this.getDefaultExportFormatter();

        return [
            {
                extend: 'print',
                text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: ':not(.not_include)',
                    format: { body: formatter }
                }
            },
            {
                extend: 'csv',
                text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>CSV</span>`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: ':not(.not_include)',
                    format: { body: formatter }
                }
            },
            {
                extend: 'excel',
                text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-spreadsheet me-1"></i>Excel</span>`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: ':not(.not_include)',
                    format: { body: formatter }
                }
            },
            {
                extend: 'pdf',
                text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-description me-1"></i>PDF</span>`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: ':not(.not_include)',
                    format: { body: formatter }
                }
            },
            {
                extend: 'copy',
                text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                className: 'dropdown-item',
                exportOptions: {
                    columns: ':not(.not_include)',
                    format: { body: formatter }
                }
            }
        ];
    }

    getTopButtons() {
        const buttons = [];

        // Export buttons
        if (this.options.showExportButtons) {
            buttons.push({
                extend: 'collection',
                className: 'btn btn-label-primary dropdown-toggle me-4',
                text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-upload icon-xs me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span></span>',
                init: function (api, node, config) {
                    $(node).removeClass('btn-secondary btn-outline-secondary');
                    $(node).addClass('btn-label-primary');
                },
                buttons: this.getExportButtons()
            });
        }

        // Create button
        if (this.options.showCreateButton) {
            buttons.push({
                text: '<span class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-plus icon-sm"></i> <span class="d-none d-sm-inline-block">Add New Record</span></span>',
                className: 'create-new btn btn-primary',
                init: function (api, node, config) {
                    $(node).removeClass('btn-secondary btn-outline-secondary');
                    $(node).addClass('btn-primary');
                },
                action: this.getCreateButtonAction()
            });
        }

        return buttons;
    }

    getCreateButtonAction() {
        // Priority: Custom callback > Modal > Route
        if (this.options.createCallback && typeof this.options.createCallback === 'function') {
            return this.options.createCallback;
        }

        if (this.options.createModal) {
            return (e, dt, node, config) => {
                $(this.options.createModal).modal('show');
            };
        }

        if (this.options.createRoute) {
            return (e, dt, node, config) => {
                window.location.href = this.options.createRoute;
            };
        }

        // Default fallback
        return (e, dt, node, config) => {
            console.warn('No create action defined. Please set createRoute, createModal, or createCallback.');
        };
    }

    getColumnDefs() {
        const columnDefs = [
            {
                // For Responsive
                className: 'control',
                orderable: false,
                searchable: false,
                responsivePriority: 2,
                targets: 0,
                render: function (data, type, full, meta) {
                    return '';
                }
            },
            {
                defaultContent: "-",
                targets: "_all"
            }
        ];

        // Add actions column if enabled
        if (this.options.showActions) {
            const actionRenderer = this.options.actionRenderer || this.getDefaultActionRenderer();

            columnDefs.push({
                targets: this.options.actionsColumnIndex,
                title: 'Actions',
                orderable: false,
                searchable: false,
                render: actionRenderer
            });
        }

        return columnDefs;
    }

    getResponsiveConfig() {
        if (!this.options.responsiveModal) {
            return true;
        }

        return {
            details: {
                display: DataTable.Responsive.display.modal({
                    header: (row) => {
                        const data = row.data();
                        const identifier = data[this.options.modalHeaderField] || data['name'] || data['title'] || 'Record';
                        return `Details of ${identifier}`;
                    }
                }),
                type: 'column',
                renderer: function (api, rowIdx, columns) {
                    const data = columns
                        .map(function (col) {
                            return col.title !== ''
                                ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                                    <td>${col.title}:</td>
                                    <td>${col.data}</td>
                                   </tr>`
                                : '';
                        })
                        .join('');

                    if (data) {
                        const div = document.createElement('div');
                        div.classList.add('table-responsive');
                        const table = document.createElement('table');
                        div.appendChild(table);
                        table.classList.add('table', 'datatables-basic');
                        const tbody = document.createElement('tbody');
                        tbody.innerHTML = data;
                        table.appendChild(tbody);
                        return div;
                    }
                    return false;
                }
            }
        };
    }

    createTable() {
        const tableTitle = this.createTableTitle();

        const config = {
            ajax: this.options.ajaxUrl,
            columns: this.options.columns,
            columnDefs: this.getColumnDefs(),
            select: {
                style: 'multi',
                selector: 'td:nth-child(2)'
            },
            order: this.options.order,
            layout: {
                top2Start: {
                    rowClass: 'row card-header flex-column flex-md-row border-bottom mx-0 px-3',
                    features: [tableTitle]
                },
                top2End: {
                    features: [{
                        buttons: this.getTopButtons()
                    }]
                },
                topStart: {
                    rowClass: 'row mx-0 px-3 my-0 justify-content-between border-bottom',
                    features: [{
                        pageLength: {
                            menu: this.options.pageLength,
                            text: 'Show_MENU_entries'
                        }
                    }]
                },
                topEnd: {
                    search: {
                        placeholder: this.options.searchPlaceholder
                    }
                },
                bottomStart: {
                    rowClass: 'row mx-3 justify-content-between',
                    features: ['info']
                },
                bottomEnd: 'paging'
            },
            language: {
                paginate: {
                    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
                    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
                    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
                    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
                }
            },
            responsive: this.getResponsiveConfig(),
            ...this.options.additionalOptions
        };

        this.dataTable = $(this.options.tableId).DataTable(config);
        return this.dataTable;
    }

    // Helper methods
    reload() {
        if (this.dataTable) {
            this.dataTable.ajax.reload();
        }
    }

    destroy() {
        if (this.dataTable) {
            this.dataTable.destroy();
        }
    }

    getSelectedRows() {
        if (this.dataTable) {
            return this.dataTable.rows({ selected: true }).data().toArray();
        }
        return [];
    }
}
