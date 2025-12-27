<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        <p class="help-block"></p>
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('email', 'Email*', ['class' => 'control-label']) !!}
        {!! Form::email('email', old('email'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        <p class="help-block"></p>
        @if($errors->has('email'))
            <p class="help-block">
                {{ $errors->first('email') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('phone', 'Phone*', ['class' => 'control-label']) !!}
        {!! Form::number('phone', (old('phone')) ? old('phone') : $doctor->phone, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('phone'))
            <p class="help-block">
                {{ $errors->first('phone') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('gender', 'Gender*', ['class' => 'control-label']) !!}
        {!! Form::select('gender', array('' => 'Select a Gender') + Config::get("constants.gender_array"), (old('gender')) ? old('gender') : $doctor->gender, [ 'class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('gender'))
            <p class="help-block">
                {{ $errors->first('gender') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('roles', 'Roles*', ['class' => 'control-label']) !!}
        {!! Form::select('roles[]', $roles, old('roles') ? old('role') : $doctor->roles()->pluck('name', 'name'), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required' => '']) !!}
        @if($errors->has('roles'))
            <p class="help-block">
                {{ $errors->first('roles') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('commission', 'Commission*', ['class' => 'control-label']) !!}
            <div class="input-group">
                {!! Form::number('commission', old('commission'), ['id' => 'commission', 'min' => '0', 'max' => '100', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                <span class="input-group-addon">%</span>
            </div>
            @if($errors->has('commission'))
                <p class="help-block">
                    {{ $errors->first('commission') }}
                </p>
            @endif
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('is_mobile', 'Is show on mobile?', ['class' => 'control-label']) !!}
        <br/>
        <label class="mt-checkbox">
            {!! Form::checkbox('is_mobile', 1, old('is_mobile')) !!} Is Mobile Active
            <span></span>
        </label>
        @if($errors->has('is_mobile'))
            <p class="help-block">
                {{ $errors->first('is_mobile') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('virtual_link', 'Virtual Link', ['class' => 'control-label']) !!}
        {!! Form::text('virtual_link', old('virtual_link'), ['class' => 'form-control inpt-focus', 'placeholder' => '']) !!}
        <p class="help-block"></p>
        @if($errors->has('virtual_link'))
            <p class="help-block">
                {{ $errors->first('virtual_link') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('Select Logo', 'Select Logo', ['class' => 'control-label']) !!}
        <br>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                <img src="{{asset('doctor_image/')}}/{{$doctor->image_src}}" alt=""/>
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail"
                 style="max-width: 200px; max-height: 150px;"></div>
            <div>
                <span class="btn default btn-file">
                      <span class="fileinput-new"> Select image </span>
                      <span class="fileinput-exists"> Change </span>
                      <input type="file" name="file" id="file">
                </span>

                <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput">Remove</a>
            </div>
        </div>
    </div>
</div>
