<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('category_id', 'Category*', ['class' => 'control-label']) !!}
        {!! Form::select('category_id', $categories, old('category_id'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('category_id'))
            <p class="help-block">
                {{ $errors->first('category_id') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('question', 'Question*', ['class' => 'control-label']) !!}
        {!! Form::text('question', old('question'), ['class' => 'form-control inpt-focus', 'placeholder' => 'Enter Question', 'required' => '']) !!}
        @if($errors->has('question'))
            <p class="help-block">
                {{ $errors->first('question') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('answer', 'Answer*', ['class' => 'control-label']) !!}
        {!! Form::text('answer', old('answer'), ['class' => 'form-control inpt-focus', 'placeholder' => 'Enter Answer', 'required' => '']) !!}
        @if($errors->has('answer'))
            <p class="help-block">
                {{ $errors->first('answer') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>