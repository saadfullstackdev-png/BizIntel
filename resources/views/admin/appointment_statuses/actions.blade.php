@if(Gate::allows('appointment_statuses_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.appointment_statuses.edit',[$appointment_statuse->id]) }}" data-target="#ajax_appointmentstatuses" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('appointment_statuses_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.appointment_statuses.destroy', $appointment_statuse->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif