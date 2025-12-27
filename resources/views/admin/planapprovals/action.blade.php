@if(Gate::allows('planapprovals_approval'))
    <a class="btn btn-xs btn-primary" href="{{ route('admin.planapprovals.approval',[$package->id]) }}">@lang('global.app_approval')</a>
@endif
@if(Gate::allows('planapprovals_manage'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.planapprovals.display',[$package->id]) }}"
       data-target="#ajax_packages" data-toggle="modal">@lang('global.app_display')</a>
@endif