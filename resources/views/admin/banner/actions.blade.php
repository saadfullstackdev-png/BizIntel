@if(Gate::allows('banners_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.banner.edit',[$banner->id]) }}" data-target="#ajax_banners" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('banners_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.banner.destroy', $banner->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif