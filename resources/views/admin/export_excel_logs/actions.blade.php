{{--<a class="btn btn-xs btn-info" href="{{ route('admin.export-logs.download-file', ['export_log_id' => $export_log->id]) }}"> Downloaded File </a>--}}

@if (Gate::allows('export_excel_manage'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'POST',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.export-logs.download-file'])) !!}
    {!! Form::hidden('export_log_id', $export_log->id) !!}
    {!! Form::submit(trans('global.app_downloadfile'), array('class' => 'btn btn-xs btn-info')) !!}
    {!! Form::close() !!}
@endif