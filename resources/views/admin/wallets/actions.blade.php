@if(Gate::allows('wallets_manage'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.wallets.display',[$wallet->id]) }}">@lang('global.app_display')</a>
@endif
@if(Gate::allows('wallets_refund'))
    <a class="btn btn-xs btn-info" href="{{ route('admin.wallets.refund_create',[$wallet->id]) }}" data-target="#ajax_refund" data-toggle="modal">@lang('global.app_refund')</a>
@endif
