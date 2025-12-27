@if(Gate::allows('packages_manage'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.bundles.detail',[$bundle->id]) }}"
       data-target="#ajax_bundles"
       data-toggle="modal">Detail</a>
@endif
@if(Gate::allows('packages_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.bundles.edit',[$bundle->id]) }}" data-target="#ajax_bundles"
       data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('packages_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.bundles.destroy', $bundle->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif