@if($setting->slug == 'sys-discounts')
    {!! Form::hidden('name', $setting->name) !!}
    {!! Form::hidden('data', $setting->data) !!}
    <?php  $min = explode(':', $setting->data); ?>
    <div class="row">
        <div class="col-md-12">
            <label>{{$setting->name}}</label>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Minimum</label>
                <input type="number" min="0" max="100" class="form-control" name="min" value={{$min[0]}}>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Maximum</label>
                <input type="number" min="0" max="100" class="form-control" name="max" value={{$min[1]}}>
            </div>
        </div>
    </div>
    <br>
@elseif($setting->slug == 'sys-documentationcharges')
    {!! Form::hidden('name', $setting->name) !!}
    {!! Form::hidden('data', $setting->data) !!}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>{{$setting->name}}</label>
                <input type="number" class="form-control" name="data" value={{$setting->data}}>
            </div>
        </div>
    </div>
    <br>
@elseif($setting->slug == 'sys-birthdaypromotion')
    {!! Form::hidden('name', $setting->name) !!}
    {!! Form::hidden('data', $setting->data) !!}
    <?php  $min = explode(':', $setting->data); ?>
    <div class="row">
        <div class="col-md-12">
            <label>{{$setting->name}}</label>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Pre Days</label>
                <input type="number" class="form-control" name="pre" value={{$min[0]}} required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Post Days</label>
                <input type="number" class="form-control" name="post" value={{$min[1]}} required>
            </div>
        </div>
    </div>
    <br>
@elseif($setting->slug == 'sys-financeediting')
    {!! Form::hidden('name', $setting->name) !!}
    {!! Form::hidden('data', $setting->data) !!}
    <?php  $min = explode(':', $setting->data); ?>
    <div class="row">
        <div class="col-md-12">
            <label>{{$setting->name}}</label>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Number of Days</label>
                <input type="number" class="form-control" name="data" value={{$setting->data}} required>
            </div>
        </div>
    </div>
    <br>
@elseif($setting->slug == 'sys-list-mode')
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::select('data', \Illuminate\Support\Facades\Config::get('constants.listing_array'), old('data'), ['class' => 'form-control', 'placeholder' => 'Choose an Option', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
    <br>

@elseif ($setting->slug == 'sys-back-date-appointment')
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::select('data',array('Disable','Enable'), old('data'), ['class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
    <br>
@elseif ($setting->slug == 'sys-current-sms-operator')
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::select('data',\Illuminate\Support\Facades\Config::get('constants.operator_array'), old('data'), ['class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
    <br>
@elseif ($setting->slug == 'sys-consultancy-invoice-medical-operator')
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::select('data',\Illuminate\Support\Facades\Config::get('constants.invoice_consultancy_medical_form'), old('data'), ['class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
    <br>
@elseif($setting->slug == 'sys-virtual-consultancy')
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::select('data',\Illuminate\Support\Facades\Config::get('constants.consultancy_type'), old('data'), ['class' => 'form-control', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
    <br>
@else
    {!! Form::hidden('name', $setting->name, ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
    <div class="form-group">
        {!! Form::label('data', $setting->name, ['class' => 'control-label']) !!}
        {!! Form::textarea('data', old('data'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('data'))
            <p class="help-block">
                {{ $errors->first('data') }}
            </p>
        @endif
    </div>
@endif