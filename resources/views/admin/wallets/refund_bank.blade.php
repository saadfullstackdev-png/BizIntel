<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_refund')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        @if($is_refund_allow > 0)
            <div class="form-group">
                {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.wallets.refund_bank_store']]) !!}
                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->
                    @include('admin.wallets.fields_refund_bank')
                </div>
                <div>
                    {!! Form::submit(trans('global.app_save'), ['class' => 'btn btn-success']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        @else
            <h2>Insufficient amount to refund</h2>
        @endif
    </div>

    <script src="{{ url('js/admin/wallets/refund.js') }}" type="text/javascript"></script>
</div>
