@if(Gate::allows('categories_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.categories.edit',[$category->id]) }}" data-target="#ajax_categories" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('categories_destroy'))
    {!! Form::open(array(
        'style' => 'display: inline-block;',
        'method' => 'DELETE',
        'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
        'route' => ['admin.categories.destroy', $category->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif