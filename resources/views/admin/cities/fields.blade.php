<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('region_id', 'Region*', ['class' => 'control-label']) !!}
        {!! Form::select('region_id', $regions, old('region_id'), ['class' => 'form-control city select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('region_id'))
            <p class="help-block">
                {{ $errors->first('region_id') }}
            </p>
        @endif
    </div>
    <div class="clearfix"></div>
    <div class="form-group col-md-6">
        {!! Form::label('is_featured', 'Make Featured*', ['class' => 'control-label']) !!}
        {!! Form::select('is_featured', array( '' => 'Choose an option', 1 => 'Yes', 0 => 'No'), old('is_featured'), ['class' => 'form-control city select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('is_featured'))
            <p class="help-block">
                {{ $errors->first('is_featured') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>