@if($cancellation_reason->active)
    {!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'PATCH',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.cancellation_reasons.inactive', $cancellation_reason->id])) !!}
    {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
    {!! Form::close() !!}
@else
    {!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'PATCH',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.cancellation_reasons.active', $cancellation_reason->id])) !!}
    {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
    {!! Form::close() !!}
@endif
{{--<a href="{{ route('admin.cancellation_reasons.edit',[$cancellation_reason->id]) }}" class="btn btn-xs btn-info">@lang('global.app_edit')</a>--}}
<a class="btn btn-xs btn-info" href="{{ route('admin.cancellation_reasons.edit',[$cancellation_reason->id]) }}"  data-target="#ajax_cancellationreason_edit" data-toggle="modal">@lang('global.app_edit')</a>

{!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'DELETE',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.cancellation_reasons.destroy', $cancellation_reason->id])) !!}
{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
{!! Form::close() !!}