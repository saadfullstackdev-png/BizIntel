@if(Gate::allows('notification_templates_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.notification_templates.edit',[$noti_temp->id]) }}"
       data-target="#ajax_notification_templates" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if ($noti_temp->is_promo)
    @if(Gate::allows('notification_templates_destroy'))
        {!! Form::open(array(
            'style' => 'display: inline-block;',
            'method' => 'DELETE',
            'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
            'route' => ['admin.notification_templates.destroy', $noti_temp->id])) !!}
        {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
        {!! Form::close() !!}
    @endif
    @if(Gate::allows('notification_templates_publish'))
        <a onclick="return confirm('Are you sure?')" class="btn btn-xs btn-info"
           href="{{ route('admin.notification_templates.publish',[$noti_temp->id]) }}">@lang('global.notification_templates.fields.publish_notification')</a>
    @endif
@endif