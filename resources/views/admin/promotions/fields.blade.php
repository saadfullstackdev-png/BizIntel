<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('use', 'Use*', ['class' => 'control-label']) !!}
        {!! Form::select('use', array( '' => 'Select', 'Yes' => 'Yes', 'No' => 'No'), old('use'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('use'))
            <p class="help-block">
                {{ $errors->first('use') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>