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
        {!! Form::label('price', 'Price*', ['class' => 'control-label']) !!}
        {!! Form::number('price', old('price'), ['min' => '0', 'step' => '1', 'class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('price'))
            <p class="help-block">
                {{ $errors->first('price') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {!! Form::label('service_id', 'Services*', ['class' => 'control-label']) !!}
        <div class="input-group">
            <select name="service_id_1" id="service_id_1" class="form-control select2">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="<?php echo $service['id'] ?>" data-name="<?php echo $service['name'] ?>" data-id="<?php echo $service['id'] ?>"><?php echo $service['name'] ?></option>
                @endforeach
            </select>
            <span class="input-group-btn">
                <button class="btn blue" type="button"><i class="fa fa-plus"></i>&nbsp;Add</button>
            </span>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table id="table_services" class="table table-striped table-bordered table-advance table-hover">
        <thead>
        <tr>
            <th>Service Name</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody id="table_services_row">

        </tbody>
    </table>
</div>
<div class="clearfix"></div>