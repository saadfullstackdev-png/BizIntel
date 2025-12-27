@if(Gate::allows('sms_templates_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.sms_templates.edit',[$sms_template->id]) }}" data-target="#ajax_smstemplating" data-toggle="modal">@lang('global.app_edit')</a>
@endif
<!-- {!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'DELETE',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.sms_templates.destroy', $sms_template->id])) !!}
{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
{!! Form::close() !!} -->