@if(Gate::allows('promotions_edit'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.promotions.edit',[$promotion->id]) }}" data-target="#ajax_promotions" data-toggle="modal">@lang('global.app_edit')</a>
@endif