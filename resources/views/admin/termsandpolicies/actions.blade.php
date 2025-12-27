@if(Gate::allows('termsandpolicies_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.termsandpolicies.edit',[$termsandpolicy->id]) }}" data-target="#ajax_termsandpolicies" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('termsandpolicies_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.termsandpolicies.destroy', $termsandpolicy->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif