    {!! Form::label('machine', 'Machine Type', ['class' => 'control-label']) !!}
<select name="machine_id" id="machine_id" style="width: 100%" class="form-control select2">
    <option value="">All</option>
    @foreach($machinetype as $machine)
        <option value="{{$machine->id}}">{{$machine->name}}</option>
    @endforeach
</select>
<span id="machine_handler"></span>

