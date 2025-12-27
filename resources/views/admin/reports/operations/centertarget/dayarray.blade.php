{!! Form::label('days', 'Day*', ['class' => 'control-label']) !!}
<select name="days_count" id="days_count" style="width: 100%" class="form-control select2">
    @for ($i = 1; $i <= $days; $i++)
        <option value="{{ $i }}">{{ $i }}</option>
    @endfor
</select>
<span id="days_count_handler"></span>



