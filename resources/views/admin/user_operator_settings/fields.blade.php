<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('url', 'URL*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->url)
            {!! Form::text('url', old('url'), ['disabled' => 'disabled', 'id' => 'url', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('url', old('url'), ['id' => 'url', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('url'))
            <p class="help-block">
                {{ $errors->first('url') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('username', 'Username*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->username)
            {!! Form::text('username', $user_operator_setting->username, ['disabled' => 'disabled', 'id' => 'username', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('username', old('username'), ['id' => 'username', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('username'))
            <p class="help-block">
                {{ $errors->first('username') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('password', 'Password*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->password)
            {!! Form::text('password', '********', ['disabled' => 'disabled', 'id' => 'password', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('password', old('password'), ['id' => 'password', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('password'))
            <p class="help-block">
                {{ $errors->first('password') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('mask', 'Mask*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->mask)
            {!! Form::text('mask', $user_operator_setting->mask, ['disabled' => 'disabled', 'id' => 'mask', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('mask', old('mask'), ['id' => 'mask', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('mask'))
            <p class="help-block">
                {{ $errors->first('mask') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('test_mode', 'Enable Test Mode', ['class' => 'control-label']) !!}
        {!! Form::select('test_mode', array('' => '', 1 => 'Yes', 0 => 'No'), old('test_mode'), ['id' => 'test_mode', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('test_mode'))
            <p class="help-block">
                {{ $errors->first('test_mode') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('string_1', 'Custom Field 1*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->string_1)
            {!! Form::text('string_1', $user_operator_setting->string_1, ['disabled' => 'disabled', 'id' => 'string_1', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('string_1', old('string_1'), ['id' => 'string_1', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('string_1'))
            <p class="help-block">
                {{ $errors->first('string_1') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('string_2', 'Custom Field 2*', ['class' => 'control-label']) !!}
        @if($user_operator_setting->string_2)
            {!! Form::text('string_2', $user_operator_setting->string_2, ['disabled' => 'disabled', 'id' => 'string_2', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @else
            {!! Form::text('string_2', old('string_2'), ['id' => 'string_2', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @endif
        @if($errors->has('string_2'))
            <p class="help-block">
                {{ $errors->first('string_2') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>