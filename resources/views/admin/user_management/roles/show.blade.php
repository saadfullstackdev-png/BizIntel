@extends('admin.layouts.master')
@section('title', 'View Role')
@section('content')
    <section>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Role Details</h4>
                <div>
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary me-1">Edit</a>
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-4">
                            <h5>Role Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Name:</label>
                                        <p>{{ $role->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created:</label>
                                        <p>{{ $role->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h5>Permissions ({{ $rolePermissions->count() }})</h5>
                            <div class="row">
                                @if($rolePermissions->count() > 0)
                                    @foreach($rolePermissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="badge bg-primary">{{ $permission->name }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <p class="text-muted">No permissions assigned</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection 