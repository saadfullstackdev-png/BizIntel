{{--Some Hidden fields that helps for refunds save--}}
<input type="hidden" name="patient_id" id="wallet_id" value="{{$patient_id}}" class="form-control">
<input type="hidden" name="wallet_id" id="wallet_id" value="{{$wallet_id}}" class="form-control">
{{--End--}}
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('amount', 'Amount', ['class' => 'control-label']) !!}
        {!! Form::text('amount', old('amount') , ['id' => 'amount', 'class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('amount'))
            <p class="help-block">
                {{ $errors->first('amount') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>