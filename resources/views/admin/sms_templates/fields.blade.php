<div class="row">
    <div class="col-md-12 form-group">
    <!--  {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!} -->
        {!! Form::hidden('name', $sms_template->name, ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-group">
        {!! Form::label('content', $sms_template->name, ['class' => 'control-label sms-temp-font']) !!}
        {!! Form::textarea('content', old('content'), ['id' => 'content', 'class' => 'form-control','placeholder' => '', 'required' => '']) !!}
        @if($errors->has('content'))
            <p class="help-block">
                {{ $errors->first('content') }}
            </p>
        @endif
    </div>
    <div class="col-md-6 form-group">
        {!! Form::label('variable', 'Variables', ['class' => 'control-label sms-temp-font']) !!}
        @if($sms_template->slug == 'invoice-ringup')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Invoices">
                    <option value="##patient_name##">Patient Name</option>
                    <option value="##service_name##">Service Name</option>
                    <option value="##created_at##">Invoice Ringup Date</option>
                    <option value="##remaining_balance##">Remaining Balance</option>
                </optgroup>
            </select>
        @elseif($sms_template->slug == 'plan-cash')
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
        @elseif($sms_template->slug == 'refund-amount')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="Refund">
                    <option value="##patient_name##">Patient Name</option>
                </optgroup>
                <optgroup label="Package Advances">
                    <option value="##cash_amount##">Cash Amount</option>
                    <option value="##created_at##">Refund Date</option>
                </optgroup>
            </select>
        @elseif($sms_template->slug == 'otp')
            <select class="form-control" id="variable" multiple style="height: 155px;">
                <optgroup label="OTP">
                    <option value="##otp##">otp</option>
                </optgroup>
            </select>
        @elseif($sms_template->slug == 'virtual-on-appointment' || $sms_template->slug == 'virtual-second-sms' || $sms_template->slug == 'virtual-third-sms')
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
        @else
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
        @endif
        <button type="button" onclick="applyVariable();" class="btn btn-warning" style="margin-top: 15px;">Apply
            Variable
        </button>
    </div>
</div>
<div class="clearfix"></div>