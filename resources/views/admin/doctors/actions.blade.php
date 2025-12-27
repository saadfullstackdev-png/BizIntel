@if(Gate::allows('doctors_allocate'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.doctors.location_manage',[$user->id]) }}" data-target="#ajax_doctors" data-toggle="modal">@lang('global.doctors.fields.location')</a>
@endif
@if(Gate::allows('doctors_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.doctors.edit',[$user->id]) }}" data-target="#ajax_doctors" data-toggle="modal">@lang('global.app_edit')</a>
@endif
@if(Gate::allows('doctors_destroy'))
    {!! Form::open(array(
     'style' => 'display: inline-block;',
     'method' => 'DELETE',
     'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
     'route' => ['admin.doctors.destroy', $user->id])) !!}
    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
    {!! Form::close() !!}
@endif
@if(Gate::allows('doctors_change_password'))
    <a class="btn btn-xs btn-warning" href="{{ route('admin.doctors.change_password',['id' => $user->id]) }}" data-target="#ajax_doctors" data-toggle="modal">@lang('global.users.fields.change_password')</a>
@endif