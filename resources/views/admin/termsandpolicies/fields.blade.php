<div class="row">
    <div class="form-group col-md-12">
         {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        <!--{!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => 'Enter Name', 'required' => '']) !!} -->

        {!! Form::select('name', ['term' => 'Term & Condition', 'policy' => 'Policy','refund' => 'Refund'], old('name'), ['class' => 'form-control inpt-focus select2', 'required' => '']) !!}

        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('description', 'Description*', ['class' => 'control-label']) !!}
        {!! Form::textarea('description', old('description'), ['class' => 'form-control inpt-focus','id'=>'editor', 'placeholder' => 'Enter Description']) !!}
        @if($errors->has('description'))
            <p class="help-block">
                {{ $errors->first('description') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>
<script>
    ClassicEditor
        .create( document.querySelector( '#editor' ) )
        .catch( error => {
            console.error( error );
        } );
</script>
