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
        {!! Form::select('parent_id', $parentAppointmentStatuses, old('parent_id'), ['onchange' => 'FormValidation.sC($(this).val())', 'id' => 'parent_id', 'class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('parent_id'))
            <span class="help-block help-block-error">
            {{ $errors->first('parent_id') }}
        </span>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 is_comment @if($errors->has('is_comment')) has-error @endif">
        {!! Form::label('is_comment', 'Ask for Comments?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox('is_comment', '1', old('is_comment'), ['id' => 'is_comment', 'placeholder' => '']) !!}Yes
            <span></span>
        </label>
        @if($errors->has('is_comment'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_comment') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 allow_message @if($errors->has('allow_message')) has-error @endif">
        {!! Form::label('allow_message', 'Allow SMS for this Status?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox('allow_message', '1', old('allow_message'), ['id' => 'allow_message', 'placeholder' => '']) !!}Yes
            <span></span>
        </label>
        @if($errors->has('allow_message'))
            <span class="help-block help-block-error">
                {{ $errors->first('allow_message') }}
            </span>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 is_default @if($errors->has('is_default')) has-error @endif">
        {!! Form::label('allow_message', 'Default Status for New Appointments?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_default', '1', ($appointment_statuse->is_default) ? true : false, ['class' => 'is_default', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_default', '0', (!$appointment_statuse->is_default) ? true: false, ['class' => 'is_default', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_default'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_default') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 is_default @if($errors->has('is_arrived')) has-error @endif">
        {!! Form::label('is_arrived', 'Default Status for Arrived Appointments?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_arrived', '1', ($appointment_statuse->is_arrived) ? true : false, ['class' => 'is_arrived', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_arrived', '0', (!$appointment_statuse->is_arrived) ? true: false, ['class' => 'is_arrived', 'placeholder' => '']) !!}
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
    <div class="form-group col-md-6 is_default @if($errors->has('is_cancelled')) has-error @endif">
        {!! Form::label('is_cancelled', 'Default Status for Cancelled Appointments?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_cancelled', '1', ($appointment_statuse->is_cancelled) ? true : false, ['class' => 'is_cancelled', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_cancelled', '0', (!$appointment_statuse->is_cancelled) ? true: false, ['class' => 'is_cancelled', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_cancelled'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_cancelled') }}
            </span>
        @endif
    </div>
    <div class="form-group col-md-6 is_default @if($errors->has('is_unscheduled')) has-error @endif">
        {!! Form::label('is_unscheduled', 'Default Status for Un-Scheduled Appointments?', ['class' => 'control-label']) !!}<br/>
        <label class="mt-radio mt-radio-outline">Yes
            {!! Form::radio('is_unscheduled', '1', ($appointment_statuse->is_unscheduled) ? true : false, ['class' => 'is_unscheduled', 'placeholder' => '']) !!}
            <span></span>
        </label>
        <label class="mt-radio mt-radio-outline">No
            {!! Form::radio('is_unscheduled', '0', (!$appointment_statuse->is_unscheduled) ? true: false, ['class' => 'is_unscheduled', 'placeholder' => '']) !!}
            <span></span>
        </label>
        @if($errors->has('is_unscheduled'))
            <span class="help-block help-block-error">
                {{ $errors->first('is_unscheduled') }}
            </span>
        @endif
    </div>
</div>