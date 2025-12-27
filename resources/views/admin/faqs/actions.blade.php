@if(Gate::allows('faqs_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.faqs.edit',[$faq->id]) }}" data-target="#ajax_faqs" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('faqs_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.faqs.destroy', $faq->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif