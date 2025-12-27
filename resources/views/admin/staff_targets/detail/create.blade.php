<style>
    .has-y-scroll{
        min-height: inherit;
        max-height: 400px;
        overflow-y: auto;
    }
</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_create')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::open(['method' => 'POST', 'id' => 'form-validation', 'route' => ['admin.staff_targets.store']]) !!}

                {!! Form::hidden('location_id', $location->id, ['id' => 'location_id']) !!}

                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                    @include('partials.messages')
                    <div class="alert alert-warning display-hide" id="staff_target_error"><button class="close" data-close="alert"></button> Please select all options to continue. </div>
                    <div class="alert alert-warning display-hide" id="staff_target_zero"><button class="close" data-close="alert"></button> No service matched, please make sure service are allocated to each resource correctly. </div>
                    <!-- Ends Form Validation Messages -->

                    <div class="row">
                        <div class="col-md-6 form-group">
                            {!! Form::label('year', 'Year*', ['class' => 'control-label']) !!}
                            <select id="year" name="year" class="form-control" onchange="CreateFormValidation.loadEndServices();" required>
                                @foreach($years as $id => $val)
                                    <option value="<?php echo $id ?>"><?php echo $val ?></option>
                                @endforeach
                            </select>
                            @if($errors->has('year'))
                                <p class="help-block">
                                    {{ $errors->first('year') }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6 form-group">
                            {!! Form::label('month', 'Month*', ['class' => 'control-label']) !!}
                            {!! Form::select('month', $months, old('month'), ['onchange' => 'CreateFormValidation.loadEndServices();', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('month'))
                                <p class="help-block">
                                    {{ $errors->first('month') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('staff_id', 'Staff Members*', ['class' => 'control-label']) !!}
                            <div class="input-group">
                                <select id="staff_id" name="staff_id" onchange="CreateFormValidation.loadEndServices();" class="form-control">
                                    <option value="">Select a Staff Member</option>
                                    @foreach($staffs as $staff)
                                        <option value="<?php echo $staff->user_id ?>"><?php echo $staff->full_name ?></option>
                                    @endforeach
                                </select>
                                <span class="input-group-btn">
                                    <button class="btn blue" type="button" onclick="CreateFormValidation.loadEndServices();"><i class="fa fa-refresh"></i>&nbsp;Load</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            {!! Form::label('total_amount', 'Total Target Amount*', ['class' => 'control-label']) !!}
                            {!! Form::number('total_amount', old('total_amount'), ['id' => 'total_amount', 'min' => '0', 'step' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('total_amount'))
                                <p class="help-block">
                                    {{ $errors->first('total_amount') }}
                                </p>
                            @endif
                        </div>
                        <div class="form-group col-md-6">
                            {!! Form::label('total_services', 'Total Target Services*', ['class' => 'control-label']) !!}
                            {!! Form::number('total_services', old('total_services'), ['id' => 'total_services', 'min' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                            @if($errors->has('total_services'))
                                <p class="help-block">
                                    {{ $errors->first('total_services') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive has-y-scroll" id="table_services"></div>
                    <div class="clearfix"></div>

                </div>
            <div style="padding-top: 10px;">
                {!! Form::submit(trans('global.app_save'), ['style' => 'display: none;', 'id' => 'save_btn', 'class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="{{ url('js/admin/staff_targets/detail/fields.js') }}" type="text/javascript"></script>