@if(Gate::allows('staff_targets_manage'))
    <a class="btn btn-xs btn-warning" href="{{ route('admin.staff_targets.target_view',[$staff_target->id]) }}" data-target="#ajax_staff_targets" data-toggle="modal">@lang('global.app_display')</a>
@endif

@if(Gate::allows('staff_targets_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.staff_targets.edit',[$staff_target->id]) }}"
       data-target="#ajax_staff_targets" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('staff_targets_destroy'))
    {!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'DELETE',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.staff_targets.destroy', $staff_target->id, 'location_id' => $staff_target->location_id])) !!}
    {!! Form::button(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger', 'type' => 'submit')) !!}
    {!! Form::close() !!}
@endif