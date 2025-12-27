@if(Gate::allows('machineType_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.machinetypes.edit',[$machinetype->id]) }}"
       data-target="#ajax_machinetypes"
       data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('machineType_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.machinetypes.destroy', $machinetype->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif