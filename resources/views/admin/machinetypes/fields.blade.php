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
    <div class="col-md-6 form-group">
        {!! Form::label('services', 'Services*', ['class' => 'control-label']) !!}
        <select name="services[]" id="services" class="form-control select2" required multiple>
            <option value="">Select Service</option>
            @foreach($Services as $id => $Service)
                @if ($id == 0) @continue; @endif
                @if($id < 0)
                    @php($tmp_id = ($id * -1))
                @else
                    @php($tmp_id = ($id * 1))
                @endif
                <option @if(in_array($Service['id'], $ServiceMachinetype)) selected="selected"
                        @endif value="@if($id < 0){{ ($id * -1) }}@else{{ $id }}@endif">@if($id < 0)
                        <b>{!! $Service['name'] !!}</b>@else{!! $Service['name'] !!}@endif</option>
            @endforeach
        </select>
        @if($errors->has('services'))
            <p class="help-block">
                {{ $errors->first('services') }}
            </p>
        @endif
    </div>
</div>