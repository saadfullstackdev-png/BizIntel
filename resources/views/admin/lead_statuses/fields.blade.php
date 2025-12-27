<div class="row">
    <div class="form-group col-md-6 @if($errors->has('name')) has-error @endif">
    {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '']) !!}
    @if($errors->has('name'))
        <span class="help-block help-block-error">
            {{ $errors->first('name') }}
        </span>
    @endif
</div>
    <div class="form-group col-md-6 @if($errors->has('parent_id')) has-error @endif">
        {!! Form::label('parent_id', 'Parent', ['class' => 'control-label']) !!}
        {!! Form::select('parent_id', $parentLeadStatuses, old('parent_id'), ['class' => 'form-control select2', 'placeholder' => '']) !!}
        @if($errors->has('parent_id'))
                <span class="help-block help-block-error">
            {{ $errors->first('parent_id') }}
        </span>
        @endif
</div>
</div>
<div class="row">
    <div class="form-group col-md-6 is_default @if($errors->has('is_default')) has-error @endif">
        {!! Form::label('is_default', 'Default for Open Leads', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_default', '1', ($lead_statuse->is_default) ? true : false, ['class' => 'is_default', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_default', '0', (!$lead_statuse->is_default) ? true: false, ['class' => 'is_default', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_default'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_default') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 is_arrived @if($errors->has('is_arrived')) has-error @endif">
        {!! Form::label('is_arrived', 'Default for Arrived Leads', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_arrived', '1', ($lead_statuse->is_arrived) ? true : false, ['class' => 'is_arrived', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_arrived', '0', (!$lead_statuse->is_arrived) ? true: false, ['class' => 'is_arrived', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_arrived'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_arrived') }}
            </span>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 is_converted @if($errors->has('is_converted')) has-error @endif">
        {!! Form::label('is_converted', 'Default for Converted Leads', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_converted', '1', ($lead_statuse->is_converted) ? true : false, ['class' => 'is_converted', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_converted', '0', (!$lead_statuse->is_converted) ? true: false, ['class' => 'is_converted', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_converted'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_converted') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 is_junk @if($errors->has('is_junk')) has-error @endif">
        {!! Form::label('is_junk', 'Default for Junk Leads', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_junk', '1', ($lead_statuse->is_junk) ? true : false, ['class' => 'is_junk', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_junk', '0', (!$lead_statuse->is_junk) ? true: false, ['class' => 'is_junk', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_junk'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_junk') }}
            </span>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 @if($errors->has('parent_id')) has-error @endif">
        <label class="mt-checkbox mt-checkbox-outline">
        <!-- {!! Form::label('is_comment', 'Comments', ['class' => 'control-label']) !!}<br/> -->
            {!! Form::checkbox('is_comment', '1', old('is_comment'), ['placeholder' => '']) !!} Ask for Comments
            <span></span>
            @if($errors->has('is_comment'))
                <span class="help-block help-block-error">
            {{ $errors->first('is_comment') }}
        </span>
            @endif
        </label>
    </div>
</div>
<div class="clearfix clear"></div>