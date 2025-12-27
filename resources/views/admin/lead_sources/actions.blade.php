@if(Gate::allows('lead_sources_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.lead_sources.edit',[$lead_source->id]) }}" data-target="#ajax_leadsources" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('lead_sources_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.lead_sources.destroy', $lead_source->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif