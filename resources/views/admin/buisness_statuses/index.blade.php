@extends('layouts.app')
@section('title', 'Goldern Ticket Statuses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Goldern Ticket Statuses</h2>
    <a href="{{ route('admin.buisness-statuses.create') }}" class="btn btn-success">Add New</a>
</div>

<table class="table table-bordered table-hover">
    <thead class="thead-dark">
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($statuses as $status)
            <tr>
                <td>{{ $loop->iteration + ($statuses->currentPage() - 1) * $statuses->perPage() }}</td>
                <td>{{ $status->name }}</td>
                <td>
                    <span class="badge badge-{{ $status->active ? 'success' : 'secondary' }}">
                        {{ $status->active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.buisness-statuses.edit', $status) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('admin.buisness-statuses.destroy', $status) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this Goldern Ticket status?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No Goldern Ticket statuses found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $statuses->links() }}
@endsection