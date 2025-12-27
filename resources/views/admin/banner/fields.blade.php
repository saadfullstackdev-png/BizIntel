<div class="row">
    <div class="col-md-4 form-group">
        {{--        {!! Form::label('Select Image', 'Select Image', ['class' => 'control-label']) !!}--}}
        <br>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                <img src="{{asset('banners_images/')}}/{{$banner->image_src}}" alt=""/>
            </div>
            <div class="fileinput-preview fileinput-exists thumbnail"
                 style="max-width: 200px; max-height: 150px;"></div>
            <div>
                <span class="btn default btn-file">
                      <span class="fileinput-new"> Select image </span>
                      <span class="fileinput-exists"> Change </span>
                      <input type="file" name="file" id="file" required>
                  </span>
                <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput">Remove</a>
            </div>
        </div>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('banner_type', 'Banner type*', ['class' => 'control-label']) !!}
        {!! Form::select('banner_type', array('' => 'Select Banner Type') + Config::get('constants.banner_type_select'), old('banner_type_select'), ['class' => 'form-control select2', 'placeholder' => '', 'required' => 'required']) !!}
        @if($errors->has('banner_type'))
            <p class="help-block">
                {{ $errors->first('banner_type') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('banner_value', 'Banner Value*', ['class' => 'control-label']) !!}
        <!-- {!! Form::text('banner_value', old('banner_value'), ['class' => 'form-control inpt-focus', 'placeholder' => 'Enter Banner Value', 'required' => 'required','readonly' => 'readonly']) !!} -->
        <span id="banner_value_div" ></span>
        @if($errors->has('banner_value'))
            <p class="help-block">
                {{ $errors->first('banner_value') }}
            </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>