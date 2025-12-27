@extends('admin.layouts.master')
@section('title', 'Roles')
@section('content')
    <section>
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="table" id="dataTable">
                    <thead>
                        <tr>
                            <th class="not_include"></th>
                            <th>Sr #</th>
                            <th>Name</th>
                            <th class="not_include">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@endsection
@section('page-js')
    @if(Session::has('error'))
        <script>
            $(document).ready(function() {
                const error = '{{ Session::get('error') }}';
                showToast("error", error);
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            const rolesTable = new GenericDataTable({
                tableId: '#dataTable',
                ajaxUrl: "{{ route('roles.index') }}",
                title: 'Roles',
                createRoute: "{{ route('roles.create') }}",
                columns: [
                    { data: 'id' },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name' },
                    { data: 'action' }
                ],
                actionRenderer: function(data, type, full, meta) {
                    let btn = '';
                    if(full.name != 'Admin') {
                        btn += `
                            <li><a href="{{ url('roles') }}/${full.id}/edit" class="dropdown-item">Edit</a></li>
                        `;
                    }

                    if(full.name != 'Admin') {
                        btn += `
                            <li><a href="javascript:;" class="dropdown-item text-danger deleteRecord" data-url="{{ url('roles') }}/${full.id}">Delete</a></li>
                        `;
                    }
                    return `
                        <div class="d-inline-block">
                            <a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="icon-base ti tabler-dots-vertical"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end m-0">
                                ${btn}
                            </ul>
                        </div>
                    `;
                },
                searchPlaceholder: 'Search roles...',
            });
        });
    </script>
@endsection
