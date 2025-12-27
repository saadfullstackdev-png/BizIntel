<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
    @if ($notification_templates->slug == null)
        <div class="col-md-6 form-group">
            {!! Form::label('slug', 'Slug*', ['class' => 'control-label']) !!}
            {!! Form::text('slug', old('slug'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
            @if($errors->has('slug'))
                <p class="help-block">
                    {{ $errors->first('slug') }}
                </p>
            @endif
        </div>
    @else
        <div class="col-md-6 form-group">
            {{--{!! Form::label('slug', 'Slug*', ['class' => 'control-label']) !!}--}}
            {!! Form::hidden('slug', old('slug'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
            @if($errors->has('slug'))
                <p class="help-block">
                    {{ $errors->first('slug') }}
                </p>
            @endif
        </div>
    @endif
</div>
<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('content', $notification_templates->name, ['class' => 'control-label sms-temp-font']) !!}
        {!! Form::textarea('content', old('content'), ['id' => 'content', 'class' => 'form-control','placeholder' => '', 'required' => '']) !!}
        @if($errors->has('content'))
            <p class="help-block">
                {{ $errors->first('content') }}
            </p>
        @endif
    </div>
    <div class="col-md-6 form-group">
        {!! Form::label('variable', 'Variables', ['class' => 'control-label sms-temp-font']) !!}
        @if($notification_templates->slug == 'invoice-ringup')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Invoices">
                    <option value="##patient_name##">Patient Name</option>
                    <option value="##service_name##">Service Name</option>
                    <option value="##created_at##">Invoice Ringup Date</option>
                    <option value="##remaining_balance##">Remaining Balance</option>
                </optgroup>
            </select>
        @elseif($notification_templates->slug == 'plan-cash')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Plans">
                    <option value="##id##">Plan Id</option>
                    <option value="##patient_name##">Patient Name</option>
                </optgroup>
                <optgroup label="Package Advances">
                    <option value="##cash_amount##">Cash Amount</option>
                    <option value="##created_at##">Amount Received Date</option>
                </optgroup>
            </select>
        @elseif($notification_templates->slug == 'refund-amount')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Refund">
                    <option value="##patient_name##">Patient Name</option>
                </optgroup>
                <optgroup label="Package Advances">
                    <option value="##cash_amount##">Cash Amount</option>
                    <option value="##created_at##">Refund Date</option>
                </optgroup>
            </select>
        @elseif($notification_templates->slug == 'otp')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="OTP">
                    <option value="##otp##">otp</option>
                </optgroup>
            </select>
        @elseif($notification_templates->slug == 'virtual-on-appointment' || $notification_templates->slug == 'virtual-second-sms' || $notification_templates->slug == 'virtual-third-sms')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Appointments">
                    <option value="##patient_name##">Patient Name</option>
                    <option value="##patient_phone##">Patient Phone</option>
                    <option value="##doctor_name##">Doctor Name</option>
                    <option value="##doctor_profile_link##">Doctor Profile Link</option>
                    <option value="##appointment_date##">Appointment Date</option>
                    <option value="##appointment_time##">Appointment Time</option>
                    <option value="##appointment_service##">Appointment Service</option>
                    <option value="##fdo_name##">FDO Name</option>
                    <option value="##fdo_phone##">FDO Phone</option>
                    <option value="##centre_name##">Centre Name</option>
                    <option value="##centre_address##">Centre Address</option>
                    <option value="##centre_google_map##">Centre Google Map</option>
                </optgroup>
                <optgroup label="Leads">
                    <option value="##name##">Full Name</option>
                    <option value="##email##">Email</option>
                    <option value="##phone##">Phone</option>
                    <option value="##gender##">Gender</option>
                    <option value="##city_name##">City</option>
                    <option value="##lead_source_name##">Lead Source</option>
                    <option value="##lead_status_name##">Lead Status</option>
                </optgroup>
                <optgroup label="Others">
                    <option value="##head_office_phone##">Head Office Phone</option>
                </optgroup>
                <optgroup label="Virtual">
                    <option value="##virtual_link##">Virtual Link</option>
                </optgroup>
            </select>
        @elseif ($notification_templates->slug == 'on-appointment' || $notification_templates->slug == 'second-sms' ||  $notification_templates->slug == 'third-sms' || $notification_templates->slug == 'treatment-on-appointment' || $notification_templates->slug == 'treatment-second-sms' || $notification_templates->slug == 'treatment-third-sms')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Appointments">
                    <option value="##patient_name##">Patient Name</option>
                    <option value="##patient_phone##">Patient Phone</option>
                    <option value="##doctor_name##">Doctor Name</option>
                    <option value="##doctor_profile_link##">Doctor Profile Link</option>
                    <option value="##appointment_date##">Appointment Date</option>
                    <option value="##appointment_time##">Appointment Time</option>
                    <option value="##appointment_service##">Appointment Service</option>
                    <option value="##fdo_name##">FDO Name</option>
                    <option value="##fdo_phone##">FDO Phone</option>
                    <option value="##centre_name##">Centre Name</option>
                    <option value="##centre_address##">Centre Address</option>
                    <option value="##centre_google_map##">Centre Google Map</option>
                </optgroup>
                <optgroup label="Leads">
                    <option value="##name##">Full Name</option>
                    <option value="##email##">Email</option>
                    <option value="##phone##">Phone</option>
                    <option value="##gender##">Gender</option>
                    <option value="##city_name##">City</option>
                    <option value="##lead_source_name##">Lead Source</option>
                    <option value="##lead_status_name##">Lead Status</option>
                </optgroup>
                <optgroup label="Others">
                    <option value="##head_office_phone##">Head Office Phone</option>
                </optgroup>
            </select>
        @elseif ($notification_templates->slug == null || $notification_templates->is_promo)
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup></optgroup>
            </select>
        @endif
        <button type="button" onclick="applyVariable();" class="btn btn-warning" style="margin-top: 15px;">Apply
            Variable
        </button>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-group">
        {!! Form::label('Select Image', 'Select Image', ['class' => 'control-label']) !!}
        <br>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                <img src="{{asset('notification_templates_images/')}}/{{$notification_templates->image_url}}" alt=""/>
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
<div class="clearfix"></div>