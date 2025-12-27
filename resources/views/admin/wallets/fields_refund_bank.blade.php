{{--Some Hidden fields that helps for refunds save--}}
<input type="hidden" name="wallet_id" id="wallet_id" value="{{$wallet_meta->wallet_id}}" class="form-control">
<input type="hidden" name="wallet_meta_id" id="wallet_meta_id" value="{{$wallet_meta->id}}" class="form-control">
<input type="hidden" name="transaction_id" id="transaction_id" value="{{$wallet_meta->transaction_id}}" class="form-control">
{{--End--}}
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('refund_note', 'Refund Note', ['class' => 'control-label']) !!}
        <textarea name="refund_note" class="form-control" style="width: 650px; height: 129px;"
                  placeholder="Enter Reason Here" required></textarea>
        @if($errors->has('refund_note'))
            <p class="help-block">
                {{ $errors->first('refund_note') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('refund_amount', 'Refund Amount', ['class' => 'control-label']) !!}
        {!! Form::text('refund_amount',$wallet_meta->cash_amount, ['class' => 'form-control inpt-focus', 'placeholder' => '', 'readonly' =>'true']) !!}
        @if($errors->has('refund_amount'))
            <p class="help-block">
                {{ $errors->first('refund_amount') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>