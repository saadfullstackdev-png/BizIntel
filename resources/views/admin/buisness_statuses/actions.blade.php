@can('business_statuses_edit')
    <a href="{{ route('admin.business-statuses.edit', $status->id) }}" class="btn btn-xs btn-info" data-toggle="modal" data-target="#ajax_modal">Edit</a>
@endcan
@can('business_statuses_destroy')
    <form method="POST" action="{{ route('admin.business-statuses.destroy', $status->id) }}" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Delete?')">Delete</button>
    </form>
@endcan