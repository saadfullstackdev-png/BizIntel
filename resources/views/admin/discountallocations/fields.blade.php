<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('user_id', 'User*', ['class' => 'control-label']) !!}
        {!! Form::select('user_id', $users, old('user_id'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('user_id'))
            <p class="help-block">
                {{ $errors->first('user_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('discount_id', 'Discount*', ['class' => 'control-label']) !!}
        {!! Form::select('discount_id', $discounts, old('discount_id'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('discount_id'))
            <p class="help-block">
                {{ $errors->first('discount_id') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>