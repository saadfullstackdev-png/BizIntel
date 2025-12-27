@extends('admin.layouts.master')
@section('title', 'Users')
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
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="not_include">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modelHeading">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('users.store') }}" method="POST" class="ajax-form">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label" for="name">Name<b class="text-danger">*</b></label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter name" required />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="email">Email<b class="text-danger">*</b></label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="phone">Phone<b class="text-danger">*</b></label>
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone" required />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="password">Password<b class="text-danger">*</b></label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required />
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label" for="password_confirmation">Confirm Password<b class="text-danger">*</b></label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Enter password" required />
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="role">Role<b class="text-danger">*</b></label>
                                    <select name="role" id="role" class="form-control select2" data-placeholder="Select Role" data-allow-clear="true" required>
                                        <option value=""></option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page-js')
    <script>
        $(document).ready(function() {
            const rolesTable = new GenericDataTable({
                tableId: '#dataTable',
                ajaxUrl: "{{ route('users.index') }}",
                createModal: '#addModal',
                title: 'Users',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        render: function (data, type, full, meta) {
                            const userImg = full['profile_image'];
                            const name = full['name'];
                            const role = full['roles'][0]['name'];
                            let output;

                            if (userImg) {
                            // For Avatar image
                            output = `<img src="${assetsPath}img/avatars/${userImg}" alt="Avatar" class="rounded-circle">`;
                            } else {
                            // For Avatar badge
                            const stateNum = Math.floor(Math.random() * 6);
                            const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                            const state = states[stateNum];
                            let initials = name.match(/\b\w/g) || [];
                            // initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
                            initials = initials.join('').toUpperCase();
                            output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
                            }

                            // Creates full output for row
                            const rowOutput = `
                            <div class="d-flex justify-content-start align-items-center user-name">
                                <div class="avatar-wrapper">
                                <div class="avatar me-2">
                                    ${output}
                                </div>
                                </div>
                                <div class="d-flex flex-column">
                                <span class="emp_name text-truncate text-heading fw-medium">${name}</span>
                                <small class="emp_post text-truncate">${role}</small>
                                </div>
                            </div>
                            `;

                            return rowOutput;
                        }
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'roles',
                        render: function(data, type, full, meta) {
                            return data.map(role => `<span class="badge bg-label-primary">${role.name}</span>`).join(' ');
                        }
                    },
                    {
                        data: 'is_active',
                        render: function(data, type, full, meta) {
                            const checked = data ? 'checked' : '';
                            return `<label class="switch switch-success">
                            <input type="checkbox" class="switch-input status-toggle" ${checked} data-id="${full.id}" />
                            <span class="switch-toggle-slider">
                              <span class="switch-on">
                                <i class="icon-base ti tabler-check"></i>
                              </span>
                              <span class="switch-off">
                                <i class="icon-base ti tabler-x"></i>
                              </span>
                            </span>
                            <span class="switch-label">${data ? 'Active' : 'Inactive'}</span>
                          </label>`;
                        }
                    },
                    {
                        data: 'action'
                    }
                ],
                actionRenderer: function(data, type, full, meta) {
                    let btn = '';
                        btn += `
                        <li><a href="javascript:;" class="dropdown-item editUser" data-id="${full.id}">Edit</a></li>
                    `;

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
            });

            $('body').on('click', '.editUser', function () {
                blockUI();
                var user_id = $(this).data('id');
                $.get("{{ url('users') }}" +'/' + user_id +'/edit', function (data) {
                    unblockUI();
                    $('#modelHeading').text("Edit User");
                    $('#addModal').modal('show');
                    $('form').attr('action', "{{ url('users') }}" + '/' + user_id);
                    $('form').append('<input type="hidden" name="_method" value="PUT">');
                    $('#name').val(data.user.name);
                    $('#email').val(data.user.email);
                    $('#phone').val(data.user.phone);
                    $('#role').val(data.user.roles[0].name).trigger('change');
                    $('#password, #password_confirmation').attr('required', false);
                })
            });

            // Status toggle handler
            $('body').on('change', '.status-toggle', function() {
                const userId = $(this).data('id');
                const isActive = $(this).prop('checked') ? 1 : 0;

                blockUI();
                $.ajax({
                    url: "{{ route('users.update-status') }}",
                    type: "POST",
                    data: {
                        user_id: userId,
                        is_active: isActive,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        unblockUI();
                        rolesTable.reload();
                        showToast("success", response.message);
                    },
                    error: function(error) {
                        unblockUI();
                        showToast("error", "Something went wrong!");
                    }
                });
            });
        });
    </script>
@endsection
