<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('payment_type', 'Payment Type*', ['class' => 'control-label']) !!}
        {!! Form::select('payment_type', array('' => 'Select Payment Type') + Config::get('constants.payment_type'), old('payment_type'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('payment_type'))
            <p class="help-block">
                {{ $errors->first('payment_type') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('type', 'Use Type*', ['class' => 'control-label']) !!}
        {!! Form::select('type', array('' => 'Select Use Type') + Config::get('constants.payment_use_type'), old('type'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('type'))
            <p class="help-block">
                {{ $errors->first('type') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>