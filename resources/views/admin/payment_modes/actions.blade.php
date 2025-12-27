@if(Gate::allows('payment_modes_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.payment_modes.edit',[$payment_mode->id]) }}"
       data-target="#ajax_payment_modes" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('payment_modes_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.payment_modes.destroy', $payment_mode->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif