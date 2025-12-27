{{--@if($packagesadvances->active)--}}
{{--{!! Form::open(array(--}}
{{--'style' => 'display: inline-block;',--}}
{{--'method' => 'PATCH',--}}
{{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
{{--'route' => ['admin.packagesadvances.inactive', $packagesadvances->id])) !!}--}}
{{--{!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}--}}
{{--{!! Form::close() !!}--}}
{{--@else--}}
{{--{!! Form::open(array(--}}
{{--'style' => 'display: inline-block;',--}}
{{--'method' => 'PATCH',--}}
{{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
{{--'route' => ['admin.packagesadvances.active', $packagesadvances->id])) !!}--}}
{{--{!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}--}}
{{--{!! Form::close() !!}--}}
{{--@endif--}}
{{--
{!! Form::open(array(
    'style' => 'display: inline-block;',
    'method' => 'DELETE',
    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
    'route' => ['admin.packagesadvances.destroy', $packagesadvances->id])) !!}
{!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
{!! Form::close() !!}
--}}
{{--@if($packagesadvances->invoice_id)--}}
    {{--<label class="label label-default label-sm">invoice</label>--}}

{{--@elseif($packagesadvances->is_refund == '1' && $packagesadvances->is_cancel == '0')--}}
    {{--<a class="btn btn-xs btn-info" href="{{ route('admin.packagesadvances.edit',[$packagesadvances->id]) }}"--}}
       {{--data-target="#ajax_packagesadvances_edit" data-toggle="modal">@lang('global.app_edit')</a>--}}
    {{--{!! Form::open(array(--}}
        {{--'style' => 'display: inline-block;',--}}
        {{--'method' => 'CANCEL',--}}
        {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
        {{--'route' => ['admin.packagesadvances.cancel', $packagesadvances->id])) !!}--}}
    {{--{!! Form::submit(trans('global.app_cancel'), array('class' => 'btn btn-xs btn-danger')) !!}--}}
    {{--{!! Form::close() !!}--}}
{{--@elseif($packagesadvances->is_cancel == '1')--}}
    {{--<label class="label label-warning label-sm">Cancel</label>--}}
{{--@else--}}
    {{--<a class="btn btn-xs btn-info" href="{{ route('admin.packagesadvances.edit',[$packagesadvances->id]) }}"--}}
       {{--data-target="#ajax_packagesadvances_edit" data-toggle="modal">@lang('global.app_edit')</a>--}}

    {{--{!! Form::open(array(--}}
        {{--'style' => 'display: inline-block;',--}}
        {{--'method' => 'CANCEL',--}}
        {{--'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",--}}
        {{--'route' => ['admin.packagesadvances.cancel', $packagesadvances->id])) !!}--}}
    {{--{!! Form::submit(trans('global.app_cancel'), array('class' => 'btn btn-xs btn-danger')) !!}--}}
    {{--{!! Form::close() !!}--}}
{{--@endif--}}
