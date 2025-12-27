@if(Gate::allows('discountallocations_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.discountallocations.edit',[$discountallocation->id]) }}" data-target="#ajax_discountallocations" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('discountallocations_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.discountallocations.destroy', $discountallocation->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif