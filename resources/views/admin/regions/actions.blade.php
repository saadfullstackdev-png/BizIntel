@if(Gate::allows('regions_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.regions.edit',[$region->id]) }}" data-target="#ajax_regions" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('regions_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.regions.destroy', $region->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif