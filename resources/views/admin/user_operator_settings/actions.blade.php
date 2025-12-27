@if(Gate::allows('user_operator_settings_edit'))
    <a class="btn btn-xs btn-info"
       href="{{ route('admin.user_operator_settings.edit',[encrypt([$operator->id])]) }}" data-target="#ajax_operators" data-toggle="modal">@lang('global.app_edit')</a>
@endif