<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_edit')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($bundle, ['method' => 'PUT', 'id' => 'form-validation', 'enctype' => 'multipart/form-data', 'route' => ['admin.bundles.update', $bundle->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
            @include('partials.messages')
            <!-- Ends Form Validation Messages -->


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
                        {!! Form::label('price', 'Offered Price*', ['class' => 'control-label']) !!}
                        {!! Form::number('price', old('price'), ['min' => '0', 'step' => '1', 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('price'))
                            <p class="help-block">
                                {{ $errors->first('price') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        {!! Form::label('Select Image', 'Bundle Image', ['class' => 'control-label']) !!}
                        <br>
                        <div class="fileinput fileinput-new" data-provides="fileinput">
                            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                                <img src="{{asset('bundle_images/')}}/{{$bundle->image_src}}" alt=""/>
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
                    <div class="form-group col-md-6">
                        <label>Tax</label>
                        <div class="mt-radio-list">
                            @foreach($tax_treatment_types as $tax_treatment_type)
                                <label class="mt-radio">{{$tax_treatment_type->name}}
                                    <input type="radio" value="{{$tax_treatment_type->id}}"
                                           name="tax_treatment_type_id" {{ ($bundle->tax_treatment_type_id == $tax_treatment_type->id)? "checked" : "" }}/>
                                    <span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('apply_discount', 'Apply Discounts on this Package?', ['class' => 'control-label']) !!}
                        <br/>
                        <label class="mt-checkbox">
                            {!! Form::checkbox('apply_discount', 1, old('apply_discount')) !!}Apply Discounts
                            <span></span>
                        </label>
                        @if($errors->has('apply_discount'))
                            <p class="help-block">
                                {{ $errors->first('apply_discount') }}
                            </p>
                        @endif
                    </div>
                    <div class="form-group col-md-6">
                        <label>Is show on mobile?</label>
                        <div class="mt-radio-list">
                            @foreach($content_display_types as $content_display_type)
                                <label class="mt-radio">{{$content_display_type->name}}
                                    <input type="radio" value="{{$content_display_type->id}}"
                                           name="is_mobile" {{ ($bundle->is_mobile == $content_display_type->id)? "checked" : "" }}/>
                                    <span></span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('Description', 'description', ['class' => 'control-label']) !!}
                        {!! Form::text('description', old('description'), ['class' => 'form-control', 'placeholder' => '']) !!}
                        @if($errors->has('description'))
                            <p class="help-block">
                                {{ $errors->first('description') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        {!! Form::label('service_id', 'Services*', ['class' => 'control-label']) !!}
                        <div class="input-group">
                            <select id="service_id" class="form-control select2">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    @if($service['slug'] == 'all')
                                        @continue
                                    @endif
                                    <option value="<?php echo $service['id'] ?>"
                                            data-name="<?php echo $service['name'] ?>"
                                            data-price="<?php echo $service['price'] ?>"
                                            data-id="<?php echo $service['id'] ?>"><?php echo $service['name'] ?></option>
                                @endforeach
                            </select>
                            <span class="input-group-btn"><button class="btn blue" type="button"
                                                                  onclick="FormValidation.addRow();"><i
                                            class="fa fa-plus"></i>&nbsp;Add</button></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('services_price', 'Services Price*', ['class' => 'control-label']) !!}
                        {!! Form::number('services_price', old('services_price'), ['id' => 'services_price', 'min' => '0', 'step' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('services_price'))
                            <p class="help-block">
                                {{ $errors->first('services_price') }}
                            </p>
                        @endif
                    </div>
                    <div class="form-group col-md-6">
                        {!! Form::label('total_services', 'Total Services*', ['class' => 'control-label']) !!}
                        {!! Form::number('total_services', old('total_services'), ['id' => 'total_services', 'min' => '1', 'readonly' => true, 'class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        @if($errors->has('total_services'))
                            <p class="help-block">
                                {{ $errors->first('total_services') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table_services" class="table table-striped table-bordered table-advance table-hover">
                        <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Price</th>
                            <th width="10%">Action</th>
                        </tr>
                        </thead>
                        @php($counter = 0)
                        @if($relationships)
                            @foreach($relationships as $relationship)
                                @if(array_key_exists($relationship->service_id, $bundle_services))
                                    @php( $counter = $counter + 1)
                                    <tr id="singleRow{{ $counter }}">
                                        <td>
                                            <input type="hidden"
                                                   value="{{ $bundle_services[$relationship->service_id]->id }}"
                                                   id="serviceID{{ $counter }}" name="service_id[{{ $counter }}]"/>
                                            <span id="serviceText{{ $counter }}">&nbsp;&nbsp;&nbsp;{{ $bundle_services[$relationship->service_id]->name }}</span>
                                        </td>
                                        <td>
                                            <input type="hidden" class="servicePriceValue"
                                                   value="{{ $relationship->service_price }}"
                                                   id="servicePriceValue{{ $counter }}"
                                                   name="service_price[{{ $counter }}]"/>
                                            <span id="servicePrice{{ $counter }}">{{ $relationship->service_price }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-xs btn-danger" type="button"
                                                    onclick="FormValidation.deleteRow('{{ $counter }}')">
                                                <i class="fa fa-trash"></i>&nbsp;Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                    </table>
                </div>
                <div class="clearfix"></div>

            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
        <script src="{{ url('js/admin/bundles/fields.js') }}" type="text/javascript"></script>
    </div>
</div>
<input type="hidden" value="{{ count($relationships) }}" id="total_servicesCount"/>
<table id="rowGenerator" style="display: none;">
    <tr id="singleRowAAA">
        <td>
            <input type="hidden" value="" id="serviceIDAAA" name="service_id[AAA]"/>
            <span id="serviceTextAAA"></span>
        </td>
        <td>
            <input type="hidden" class="servicePriceValueBBB" value="" id="servicePriceValueAAA"
                   name="service_price[AAA]"/>
            <span id="servicePriceAAA"></span>
        </td>
        <td>
            <button class="btn btn-xs btn-danger" type="button" onclick="FormValidation.deleteRow('AAA')">
                <i class="fa fa-trash"></i>&nbsp;Delete
            </button>
        </td>
    </tr>
</table>
<script>
    $('#service_id').select2({
        dropdownParent: $('#ajax_bundles .modal-content'), // Target the modal-content specifically
        width: '100%'
    });
</script>