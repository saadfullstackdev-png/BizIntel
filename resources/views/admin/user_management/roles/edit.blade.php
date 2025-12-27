@extends('admin.layouts.master')
@section('title', 'Edit Role')
@section('content')
    <section>
        <div class="card">
            <div class="card-header py-1">
                <h4 class="card-title mb-0">Edit Role</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('roles.update', $role->id) }}" method="POST" class="ajax-form"
                    data-redirect="{{ route('roles.index') }}" id="roleForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 mb-4">
                            <label class="form-label" for="name">Role Name<b class="text-danger">*</b></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter role name" value="{{ $role->name }}" required />
                        </div>

                        <div class="col-12">
                            <h5 class="mb-4">Role Permissions</h5>
                            <!-- Permission -->
                            <div class="row">
                                @foreach ($permissions as $parent => $childs)
                                    <div class="col-6 d-flex flex-column justify-content-between" style="min-height: 100%;">
                                        <div>
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="{{ $parent }}" onchange="checkAll(this)">
                                                    <label class="form-check-label fw-bold fs-5" for="{{ $parent }}">{{ $parent }}</label>
                                                </div>
                                            </div>

                                            @foreach ($childs['permissions'] as $permission)
                                                <div class="col-12">
                                                    <div class="form-check">
                                                        <input class="custom-control-input child" type="checkbox" id="{{ $permission->name }}" name="permissions[]" value="{{ $permission->name }}" data-parent="{{ $parent }}" onchange="updateParentCheckbox(this)" @if(in_array($permission->id, $role->permissions)) checked @endif>
                                                        <label class="form-check-label" for="{{ $permission->name }}">{{ $permission->display_name }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <hr class="mt-auto">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 mt-3 text-center">
                            <button type="submit" class="btn btn-primary me-1">Update Role</button>
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('page-js')
    <script>
        function checkAll(element) {
        var parent = $(element).attr('id');
        if ($(element).is(':checked')) {
            $('input[data-parent="' + parent + '"]').prop('checked', true);
        } else {
            $('input[data-parent="' + parent + '"]').prop('checked', false);
        }
    }

    function updateParentCheckbox(child) {
        let parentId = child.getAttribute("data-parent");
        let parentCheckbox = document.getElementById(parentId);
        let childCheckboxes = document.querySelectorAll(`input[data-parent='${parentId}']`);

        // Check if all child checkboxes are checked
        let allChecked = Array.from(childCheckboxes).every(checkbox => checkbox.checked);
        parentCheckbox.checked = allChecked;
    }
    </script>
@endsection
