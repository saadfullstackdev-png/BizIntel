{{--@if(Gate::allows('feedbacks_edit'))--}}
{{--    <a class="btn btn-xs btn-info" href="{{ route('admin.feedbacks.edit',[$feedback->id]) }}" data-target="#ajax_feedbacks" data-toggle="modal">@lang('global.app_edit')</a>--}}
{{--@endif--}}

@if(Gate::allows('feedbacks_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.feedbacks.destroy', $feedback->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
