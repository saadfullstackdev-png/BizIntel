@if(Gate::allows('package_selling_manage'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.packagesellings.display',[$package->id]) }}" data-target="#ajax_packageselling" data-toggle="modal">@lang('global.app_display')</a>
@endif