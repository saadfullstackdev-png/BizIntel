@if(Gate::allows('pabao_records_payment'))
    @if($outstanding_amount>0)
        <a class="btn btn-xs btn-info" href="{{ route('admin.pabao_records.create_payment',[$pabao_record->id]) }}" data-target="#ajax_add_payment_pabau" data-toggle="modal">@lang('global.app_payment')</a>
    @endif
@endif

@if(Gate::allows('pabao_records_detail'))
    <a class="btn btn-xs btn-success" href="{{ route('admin.pabao_records.detail_payment',[$pabao_record->id]) }}" data-target="#ajax_detail_pabau" data-toggle="modal">@lang('global.app_detail')</a>
@endif