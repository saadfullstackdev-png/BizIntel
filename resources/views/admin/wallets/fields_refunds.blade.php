{{--Some Hidden fields that helps for refunds save--}}
<input type="hidden" name="wallet_id" id="wallet_id" value="{{$id}}" class="form-control">
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
        {!! Form::label('balance', 'Balance', ['class' => 'control-label']) !!}
        {!! Form::text('balance',$refund_amount, ['class' => 'form-control inpt-focus', 'placeholder' => '', 'readonly' =>'true']) !!}
        @if($errors->has('balance'))
            <p class="help-block">
                {{ $errors->first('balance') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('refund_amount', 'Refund Amount', ['class' => 'control-label']) !!}
        <input type="number" name="refund_amount" id="refund_amount" class="form-control"
               max="<?php echo filter_var($refund_amount, FILTER_SANITIZE_NUMBER_INT); ?>">
        @if($errors->has('refund_amount'))
            <p class="help-block">
                {{ $errors->first('refund_amount') }}
            </p>
        @endif
    </div>
</div>
{{--We take decision on that we need or not--}}
{{--<div class="row">--}}
{{--    <div class="form-group col-md-6">--}}
{{--        {!! Form::label('created_at', 'Date*', ['class' => 'control-label']) !!}--}}
{{--        <input type="text" name="created_at" value="" class="form-control date_to_refund" id="created_at" required>--}}
{{--        @if($errors->has('created_at'))--}}
{{--            <p class="help-block">--}}
{{--                {{ $errors->first('created_at') }}--}}
{{--            </p>--}}
{{--        @endif--}}
{{--    </div>--}}
{{--</div>--}}
<div class="clearfix"></div>