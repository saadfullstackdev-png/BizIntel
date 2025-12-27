@if($status->active)
    @can('business_statuses_inactive')
        <form method="POST" action="{{ route('admin.business-statuses.inactive', $status->id) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-xs btn-warning" onclick="return confirm('Sure?')">Inactive</button>
        </form>
    @else
        Active
    @endcan
@else
    @can('business_statuses_active')
        <form method="POST" action="{{ route('admin.business-statuses.active', $status->id) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-xs btn-primary" onclick="return confirm('Sure?')">Active</button>
        </form>
    @else
        Inactive
    @endcan
@endif