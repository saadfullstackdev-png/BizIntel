@if(Gate::allows('staff_targets_manage'))
    <a class="btn btn-xs btn-primary"
       href="{{ route('admin.staff_targets.detail',[$staff_target->id]) }}">@lang('global.app_detail')</a>
@endif