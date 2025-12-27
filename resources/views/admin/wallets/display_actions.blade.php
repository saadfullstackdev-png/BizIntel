@if($is_refund == 'yes')
    @if(Gate::allows('wallets_refund'))
        <a class="btn btn-xs btn-warning" href="{{ route('admin.wallets.refund_bank',[$advances->id]) }}"
           data-target="#ajax_wallets" data-toggle="modal">@lang('global.app_refund')</a>
    @endif
@elseif($is_refund == 'no')
    @if(Gate::allows('wallets_reverse'))
        <a class="btn btn-xs btn-danger" href="{{ route('admin.wallets.reverse_bank',[$advances->id]) }}"
           data-target="#ajax_wallets" data-toggle="modal">@lang('global.app_reverse')</a>
    @endif
@endif
