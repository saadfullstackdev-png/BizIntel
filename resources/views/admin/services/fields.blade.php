<div class="row">
    <div class="form-group col-md-6 remvwidth">
        {!! Form::label('parent_id', 'Parent*', ['class' => 'control-label']) !!}
        <select name="parent_id" class="form-control select2" id="parent_id">
            <option value="0">Parent Service</option>
            {!! $Services !!}
        </select>
        @if($errors->has('parent_id'))
            <p class="help-block">
                {{ $errors->first('parent_id') }}
            </p>
        @endif
    </div>

    <div class="form-group col-md-6">
        {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>

<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('parent_id', 'Duration*', ['class' => 'control-label']) !!}
        <select name="duration" class="form-control" id="duration">
            <option value="">Select a Duration</option>
            <option @if("00:05" == $service->duration) selected="selected" @endif value="00:05">00:05</option>
            <option @if("00:10" == $service->duration) selected="selected" @endif value="00:10">00:10</option>
            <option @if("00:15" == $service->duration) selected="selected" @endif value="00:15">00:15</option>
            <option @if("00:20" == $service->duration) selected="selected" @endif value="00:20">00:20</option>
            <option @if("00:25" == $service->duration) selected="selected" @endif value="00:25">00:25</option>
            <option @if("00:30" == $service->duration) selected="selected" @endif value="00:30">00:30</option>
            <option @if("00:35" == $service->duration) selected="selected" @endif value="00:35">00:35</option>
            <option @if("00:40" == $service->duration) selected="selected" @endif value="00:40">00:40</option>
            <option @if("00:45" == $service->duration) selected="selected" @endif value="00:45">00:45</option>
            <option @if("00:50" == $service->duration) selected="selected" @endif value="00:50">00:50</option>
            <option @if("00:55" == $service->duration) selected="selected" @endif value="00:55">00:55</option>
            <option @if("01:00" == $service->duration) selected="selected" @endif value="01:00">01:00</option>
            <option @if("01:05" == $service->duration) selected="selected" @endif value="01:05">01:05</option>
            <option @if("01:10" == $service->duration) selected="selected" @endif value="01:10">01:10</option>
            <option @if("01:15" == $service->duration) selected="selected" @endif value="01:15">01:15</option>
            <option @if("01:20" == $service->duration) selected="selected" @endif value="01:20">01:20</option>
            <option @if("01:25" == $service->duration) selected="selected" @endif value="01:25">01:25</option>
            <option @if("01:30" == $service->duration) selected="selected" @endif value="01:30">01:30</option>
            <option @if("01:35" == $service->duration) selected="selected" @endif value="01:35">01:35</option>
            <option @if("01:40" == $service->duration) selected="selected" @endif value="01:40">01:40</option>
            <option @if("01:45" == $service->duration) selected="selected" @endif value="01:45">01:45</option>
            <option @if("01:50" == $service->duration) selected="selected" @endif value="01:50">01:50</option>
            <option @if("01:55" == $service->duration) selected="selected" @endif value="01:55">01:55</option>
            <option @if("02:00" == $service->duration) selected="selected" @endif value="02:00">02:00</option>
            <option @if("02:05" == $service->duration) selected="selected" @endif value="02:05">02:05</option>
            <option @if("02:10" == $service->duration) selected="selected" @endif value="02:10">02:10</option>
            <option @if("02:15" == $service->duration) selected="selected" @endif value="02:15">02:15</option>
            <option @if("02:20" == $service->duration) selected="selected" @endif value="02:20">02:20</option>
            <option @if("02:25" == $service->duration) selected="selected" @endif value="02:25">02:25</option>
            <option @if("02:30" == $service->duration) selected="selected" @endif value="02:30">02:30</option>
            <option @if("02:35" == $service->duration) selected="selected" @endif value="02:35">02:35</option>
            <option @if("02:40" == $service->duration) selected="selected" @endif value="02:40">02:40</option>
            <option @if("02:45" == $service->duration) selected="selected" @endif value="02:45">02:45</option>
            <option @if("02:50" == $service->duration) selected="selected" @endif value="02:50">02:50</option>
            <option @if("02:55" == $service->duration) selected="selected" @endif value="02:55">02:55</option>
            <option @if("03:00" == $service->duration) selected="selected" @endif value="03:00">03:00</option>
            <option @if("03:05" == $service->duration) selected="selected" @endif value="03:05">03:05</option>
            <option @if("03:10" == $service->duration) selected="selected" @endif value="03:10">03:10</option>
            <option @if("03:15" == $service->duration) selected="selected" @endif value="03:15">03:15</option>
            <option @if("03:20" == $service->duration) selected="selected" @endif value="03:20">03:20</option>
            <option @if("03:25" == $service->duration) selected="selected" @endif value="03:25">03:25</option>
            <option @if("03:30" == $service->duration) selected="selected" @endif value="03:30">03:30</option>
            <option @if("03:35" == $service->duration) selected="selected" @endif value="03:35">03:35</option>
            <option @if("03:40" == $service->duration) selected="selected" @endif value="03:40">03:40</option>
            <option @if("03:45" == $service->duration) selected="selected" @endif value="03:45">03:45</option>
            <option @if("03:50" == $service->duration) selected="selected" @endif value="03:50">03:50</option>
            <option @if("03:55" == $service->duration) selected="selected" @endif value="03:55">03:55</option>
            <option @if("04:00" == $service->duration) selected="selected" @endif value="04:00">04:00</option>
            <option @if("04:05" == $service->duration) selected="selected" @endif value="04:05">04:05</option>
            <option @if("04:10" == $service->duration) selected="selected" @endif value="04:10">04:10</option>
            <option @if("04:15" == $service->duration) selected="selected" @endif value="04:15">04:15</option>
            <option @if("04:20" == $service->duration) selected="selected" @endif value="04:20">04:20</option>
            <option @if("04:25" == $service->duration) selected="selected" @endif value="04:25">04:25</option>
            <option @if("04:30" == $service->duration) selected="selected" @endif value="04:30">04:30</option>
            <option @if("04:35" == $service->duration) selected="selected" @endif value="04:35">04:35</option>
            <option @if("04:40" == $service->duration) selected="selected" @endif value="04:40">04:40</option>
            <option @if("04:45" == $service->duration) selected="selected" @endif value="04:45">04:45</option>
            <option @if("04:50" == $service->duration) selected="selected" @endif value="04:50">04:50</option>
            <option @if("04:55" == $service->duration) selected="selected" @endif value="04:55">04:55</option>
            <option @if("05:00" == $service->duration) selected="selected" @endif value="05:00">05:00</option>
            <option @if("05:05" == $service->duration) selected="selected" @endif value="05:05">05:05</option>
            <option @if("05:10" == $service->duration) selected="selected" @endif value="05:10">05:10</option>
            <option @if("05:15" == $service->duration) selected="selected" @endif value="05:15">05:15</option>
            <option @if("05:20" == $service->duration) selected="selected" @endif value="05:20">05:20</option>
            <option @if("05:25" == $service->duration) selected="selected" @endif value="05:25">05:25</option>
            <option @if("05:30" == $service->duration) selected="selected" @endif value="05:30">05:30</option>
            <option @if("05:35" == $service->duration) selected="selected" @endif value="05:35">05:35</option>
            <option @if("05:40" == $service->duration) selected="selected" @endif value="05:40">05:40</option>
            <option @if("05:45" == $service->duration) selected="selected" @endif value="05:45">05:45</option>
            <option @if("05:50" == $service->duration) selected="selected" @endif value="05:50">05:50</option>
            <option @if("05:55" == $service->duration) selected="selected" @endif value="05:55">05:55</option>
            <option @if("06:00" == $service->duration) selected="selected" @endif value="06:00">06:00</option>
            <option @if("06:05" == $service->duration) selected="selected" @endif value="06:05">06:05</option>
            <option @if("06:10" == $service->duration) selected="selected" @endif value="06:10">06:10</option>
            <option @if("06:15" == $service->duration) selected="selected" @endif value="06:15">06:15</option>
            <option @if("06:20" == $service->duration) selected="selected" @endif value="06:20">06:20</option>
            <option @if("06:25" == $service->duration) selected="selected" @endif value="06:25">06:25</option>
            <option @if("06:30" == $service->duration) selected="selected" @endif value="06:30">06:30</option>
            <option @if("06:35" == $service->duration) selected="selected" @endif value="06:35">06:35</option>
            <option @if("06:40" == $service->duration) selected="selected" @endif value="06:40">06:40</option>
            <option @if("06:45" == $service->duration) selected="selected" @endif value="06:45">06:45</option>
            <option @if("06:50" == $service->duration) selected="selected" @endif value="06:50">06:50</option>
            <option @if("06:55" == $service->duration) selected="selected" @endif value="06:55">06:55</option>
            <option @if("07:00" == $service->duration) selected="selected" @endif value="07:00">07:00</option>
            <option @if("07:05" == $service->duration) selected="selected" @endif value="07:05">07:05</option>
            <option @if("07:10" == $service->duration) selected="selected" @endif value="07:10">07:10</option>
            <option @if("07:15" == $service->duration) selected="selected" @endif value="07:15">07:15</option>
            <option @if("07:20" == $service->duration) selected="selected" @endif value="07:20">07:20</option>
            <option @if("07:25" == $service->duration) selected="selected" @endif value="07:25">07:25</option>
            <option @if("07:30" == $service->duration) selected="selected" @endif value="07:30">07:30</option>
            <option @if("07:35" == $service->duration) selected="selected" @endif value="07:35">07:35</option>
            <option @if("07:40" == $service->duration) selected="selected" @endif value="07:40">07:40</option>
            <option @if("07:45" == $service->duration) selected="selected" @endif value="07:45">07:45</option>
            <option @if("07:50" == $service->duration) selected="selected" @endif value="07:50">07:50</option>
            <option @if("07:55" == $service->duration) selected="selected" @endif value="07:55">07:55</option>
            <option @if("08:00" == $service->duration) selected="selected" @endif value="08:00">08:00</option>
            <option @if("08:05" == $service->duration) selected="selected" @endif value="08:05">08:05</option>
            <option @if("08:10" == $service->duration) selected="selected" @endif value="08:10">08:10</option>
            <option @if("08:15" == $service->duration) selected="selected" @endif value="08:15">08:15</option>
            <option @if("08:20" == $service->duration) selected="selected" @endif value="08:20">08:20</option>
            <option @if("08:25" == $service->duration) selected="selected" @endif value="08:25">08:25</option>
            <option @if("08:30" == $service->duration) selected="selected" @endif value="08:30">08:30</option>
            <option @if("08:35" == $service->duration) selected="selected" @endif value="08:35">08:35</option>
            <option @if("08:40" == $service->duration) selected="selected" @endif value="08:40">08:40</option>
            <option @if("08:45" == $service->duration) selected="selected" @endif value="08:45">08:45</option>
            <option @if("08:50" == $service->duration) selected="selected" @endif value="08:50">08:50</option>
            <option @if("08:55" == $service->duration) selected="selected" @endif value="08:55">08:55</option>
            <option @if("09:00" == $service->duration) selected="selected" @endif value="09:00">09:00</option>
            <option @if("09:05" == $service->duration) selected="selected" @endif value="09:05">09:05</option>
            <option @if("09:10" == $service->duration) selected="selected" @endif value="09:10">09:10</option>
            <option @if("09:15" == $service->duration) selected="selected" @endif value="09:15">09:15</option>
            <option @if("09:20" == $service->duration) selected="selected" @endif value="09:20">09:20</option>
            <option @if("09:25" == $service->duration) selected="selected" @endif value="09:25">09:25</option>
            <option @if("09:30" == $service->duration) selected="selected" @endif value="09:30">09:30</option>
            <option @if("09:35" == $service->duration) selected="selected" @endif value="09:35">09:35</option>
            <option @if("09:40" == $service->duration) selected="selected" @endif value="09:40">09:40</option>
            <option @if("09:45" == $service->duration) selected="selected" @endif value="09:45">09:45</option>
            <option @if("09:50" == $service->duration) selected="selected" @endif value="09:50">09:50</option>
            <option @if("09:55" == $service->duration) selected="selected" @endif value="09:55">09:55</option>
            <option @if("10:00" == $service->duration) selected="selected" @endif value="10:00">10:00</option>
            <option @if("10:05" == $service->duration) selected="selected" @endif value="10:05">10:05</option>
            <option @if("10:10" == $service->duration) selected="selected" @endif value="10:10">10:10</option>
            <option @if("10:15" == $service->duration) selected="selected" @endif value="10:15">10:15</option>
            <option @if("10:20" == $service->duration) selected="selected" @endif value="10:20">10:20</option>
            <option @if("10:25" == $service->duration) selected="selected" @endif value="10:25">10:25</option>
            <option @if("10:30" == $service->duration) selected="selected" @endif value="10:30">10:30</option>
            <option @if("10:35" == $service->duration) selected="selected" @endif value="10:35">10:35</option>
            <option @if("10:40" == $service->duration) selected="selected" @endif value="10:40">10:40</option>
            <option @if("10:45" == $service->duration) selected="selected" @endif value="10:45">10:45</option>
            <option @if("10:50" == $service->duration) selected="selected" @endif value="10:50">10:50</option>
            <option @if("10:55" == $service->duration) selected="selected" @endif value="10:55">10:55</option>
            <option @if("11:00" == $service->duration) selected="selected" @endif value="11:00">11:00</option>
            <option @if("11:05" == $service->duration) selected="selected" @endif value="11:05">11:05</option>
            <option @if("11:10" == $service->duration) selected="selected" @endif value="11:10">11:10</option>
            <option @if("11:15" == $service->duration) selected="selected" @endif value="11:15">11:15</option>
            <option @if("11:20" == $service->duration) selected="selected" @endif value="11:20">11:20</option>
            <option @if("11:25" == $service->duration) selected="selected" @endif value="11:25">11:25</option>
            <option @if("11:30" == $service->duration) selected="selected" @endif value="11:30">11:30</option>
            <option @if("11:35" == $service->duration) selected="selected" @endif value="11:35">11:35</option>
            <option @if("11:40" == $service->duration) selected="selected" @endif value="11:40">11:40</option>
            <option @if("11:45" == $service->duration) selected="selected" @endif value="11:45">11:45</option>
            <option @if("11:50" == $service->duration) selected="selected" @endif value="11:50">11:50</option>
            <option @if("11:55" == $service->duration) selected="selected" @endif value="11:55">11:55</option>
            <option @if("12:00" == $service->duration) selected="selected" @endif value="12:00">12:00</option>
            <option @if("12:05" == $service->duration) selected="selected" @endif value="12:05">12:05</option>
            <option @if("12:10" == $service->duration) selected="selected" @endif value="12:10">12:10</option>
            <option @if("12:15" == $service->duration) selected="selected" @endif value="12:15">12:15</option>
            <option @if("12:20" == $service->duration) selected="selected" @endif value="12:20">12:20</option>
            <option @if("12:25" == $service->duration) selected="selected" @endif value="12:25">12:25</option>
            <option @if("12:30" == $service->duration) selected="selected" @endif value="12:30">12:30</option>
            <option @if("12:35" == $service->duration) selected="selected" @endif value="12:35">12:35</option>
            <option @if("12:40" == $service->duration) selected="selected" @endif value="12:40">12:40</option>
            <option @if("12:45" == $service->duration) selected="selected" @endif value="12:45">12:45</option>
            <option @if("12:50" == $service->duration) selected="selected" @endif value="12:50">12:50</option>
            <option @if("12:55" == $service->duration) selected="selected" @endif value="12:55">12:55</option>
            <option @if("13:00" == $service->duration) selected="selected" @endif value="13:00">13:00</option>
            <option @if("13:05" == $service->duration) selected="selected" @endif value="13:05">13:05</option>
            <option @if("13:10" == $service->duration) selected="selected" @endif value="13:10">13:10</option>
            <option @if("13:15" == $service->duration) selected="selected" @endif value="13:15">13:15</option>
            <option @if("13:20" == $service->duration) selected="selected" @endif value="13:20">13:20</option>
            <option @if("13:25" == $service->duration) selected="selected" @endif value="13:25">13:25</option>
            <option @if("13:30" == $service->duration) selected="selected" @endif value="13:30">13:30</option>
            <option @if("13:35" == $service->duration) selected="selected" @endif value="13:35">13:35</option>
            <option @if("13:40" == $service->duration) selected="selected" @endif value="13:40">13:40</option>
            <option @if("13:45" == $service->duration) selected="selected" @endif value="13:45">13:45</option>
            <option @if("13:50" == $service->duration) selected="selected" @endif value="13:50">13:50</option>
            <option @if("13:55" == $service->duration) selected="selected" @endif value="13:55">13:55</option>
            <option @if("14:00" == $service->duration) selected="selected" @endif value="14:00">14:00</option>
            <option @if("14:05" == $service->duration) selected="selected" @endif value="14:05">14:05</option>
            <option @if("14:10" == $service->duration) selected="selected" @endif value="14:10">14:10</option>
            <option @if("14:15" == $service->duration) selected="selected" @endif value="14:15">14:15</option>
            <option @if("14:20" == $service->duration) selected="selected" @endif value="14:20">14:20</option>
            <option @if("14:25" == $service->duration) selected="selected" @endif value="14:25">14:25</option>
            <option @if("14:30" == $service->duration) selected="selected" @endif value="14:30">14:30</option>
            <option @if("14:35" == $service->duration) selected="selected" @endif value="14:35">14:35</option>
            <option @if("14:40" == $service->duration) selected="selected" @endif value="14:40">14:40</option>
            <option @if("14:45" == $service->duration) selected="selected" @endif value="14:45">14:45</option>
            <option @if("14:50" == $service->duration) selected="selected" @endif value="14:50">14:50</option>
            <option @if("14:55" == $service->duration) selected="selected" @endif value="14:55">14:55</option>
            <option @if("15:00" == $service->duration) selected="selected" @endif value="15:00">15:00</option>
            <option @if("15:05" == $service->duration) selected="selected" @endif value="15:05">15:05</option>
            <option @if("15:10" == $service->duration) selected="selected" @endif value="15:10">15:10</option>
            <option @if("15:15" == $service->duration) selected="selected" @endif value="15:15">15:15</option>
            <option @if("15:20" == $service->duration) selected="selected" @endif value="15:20">15:20</option>
            <option @if("15:25" == $service->duration) selected="selected" @endif value="15:25">15:25</option>
            <option @if("15:30" == $service->duration) selected="selected" @endif value="15:30">15:30</option>
            <option @if("15:35" == $service->duration) selected="selected" @endif value="15:35">15:35</option>
            <option @if("15:40" == $service->duration) selected="selected" @endif value="15:40">15:40</option>
            <option @if("15:45" == $service->duration) selected="selected" @endif value="15:45">15:45</option>
            <option @if("15:50" == $service->duration) selected="selected" @endif value="15:50">15:50</option>
            <option @if("15:55" == $service->duration) selected="selected" @endif value="15:55">15:55</option>
            <option @if("16:00" == $service->duration) selected="selected" @endif value="16:00">16:00</option>
            <option @if("16:05" == $service->duration) selected="selected" @endif value="16:05">16:05</option>
            <option @if("16:10" == $service->duration) selected="selected" @endif value="16:10">16:10</option>
            <option @if("16:15" == $service->duration) selected="selected" @endif value="16:15">16:15</option>
            <option @if("16:20" == $service->duration) selected="selected" @endif value="16:20">16:20</option>
            <option @if("16:25" == $service->duration) selected="selected" @endif value="16:25">16:25</option>
            <option @if("16:30" == $service->duration) selected="selected" @endif value="16:30">16:30</option>
            <option @if("16:35" == $service->duration) selected="selected" @endif value="16:35">16:35</option>
            <option @if("16:40" == $service->duration) selected="selected" @endif value="16:40">16:40</option>
            <option @if("16:45" == $service->duration) selected="selected" @endif value="16:45">16:45</option>
            <option @if("16:50" == $service->duration) selected="selected" @endif value="16:50">16:50</option>
            <option @if("16:55" == $service->duration) selected="selected" @endif value="16:55">16:55</option>
            <option @if("17:00" == $service->duration) selected="selected" @endif value="17:00">17:00</option>
            <option @if("17:05" == $service->duration) selected="selected" @endif value="17:05">17:05</option>
            <option @if("17:10" == $service->duration) selected="selected" @endif value="17:10">17:10</option>
            <option @if("17:15" == $service->duration) selected="selected" @endif value="17:15">17:15</option>
            <option @if("17:20" == $service->duration) selected="selected" @endif value="17:20">17:20</option>
            <option @if("17:25" == $service->duration) selected="selected" @endif value="17:25">17:25</option>
            <option @if("17:30" == $service->duration) selected="selected" @endif value="17:30">17:30</option>
            <option @if("17:35" == $service->duration) selected="selected" @endif value="17:35">17:35</option>
            <option @if("17:40" == $service->duration) selected="selected" @endif value="17:40">17:40</option>
            <option @if("17:45" == $service->duration) selected="selected" @endif value="17:45">17:45</option>
            <option @if("17:50" == $service->duration) selected="selected" @endif value="17:50">17:50</option>
            <option @if("17:55" == $service->duration) selected="selected" @endif value="17:55">17:55</option>
            <option @if("18:00" == $service->duration) selected="selected" @endif value="18:00">18:00</option>
            <option @if("18:05" == $service->duration) selected="selected" @endif value="18:05">18:05</option>
            <option @if("18:10" == $service->duration) selected="selected" @endif value="18:10">18:10</option>
            <option @if("18:15" == $service->duration) selected="selected" @endif value="18:15">18:15</option>
            <option @if("18:20" == $service->duration) selected="selected" @endif value="18:20">18:20</option>
            <option @if("18:25" == $service->duration) selected="selected" @endif value="18:25">18:25</option>
            <option @if("18:30" == $service->duration) selected="selected" @endif value="18:30">18:30</option>
            <option @if("18:35" == $service->duration) selected="selected" @endif value="18:35">18:35</option>
            <option @if("18:40" == $service->duration) selected="selected" @endif value="18:40">18:40</option>
            <option @if("18:45" == $service->duration) selected="selected" @endif value="18:45">18:45</option>
            <option @if("18:50" == $service->duration) selected="selected" @endif value="18:50">18:50</option>
            <option @if("18:55" == $service->duration) selected="selected" @endif value="18:55">18:55</option>
            <option @if("19:00" == $service->duration) selected="selected" @endif value="19:00">19:00</option>
            <option @if("19:05" == $service->duration) selected="selected" @endif value="19:05">19:05</option>
            <option @if("19:10" == $service->duration) selected="selected" @endif value="19:10">19:10</option>
            <option @if("19:15" == $service->duration) selected="selected" @endif value="19:15">19:15</option>
            <option @if("19:20" == $service->duration) selected="selected" @endif value="19:20">19:20</option>
            <option @if("19:25" == $service->duration) selected="selected" @endif value="19:25">19:25</option>
            <option @if("19:30" == $service->duration) selected="selected" @endif value="19:30">19:30</option>
            <option @if("19:35" == $service->duration) selected="selected" @endif value="19:35">19:35</option>
            <option @if("19:40" == $service->duration) selected="selected" @endif value="19:40">19:40</option>
            <option @if("19:45" == $service->duration) selected="selected" @endif value="19:45">19:45</option>
            <option @if("19:50" == $service->duration) selected="selected" @endif value="19:50">19:50</option>
            <option @if("19:55" == $service->duration) selected="selected" @endif value="19:55">19:55</option>
            <option @if("20:00" == $service->duration) selected="selected" @endif value="20:00">20:00</option>
            <option @if("20:05" == $service->duration) selected="selected" @endif value="20:05">20:05</option>
            <option @if("20:10" == $service->duration) selected="selected" @endif value="20:10">20:10</option>
            <option @if("20:15" == $service->duration) selected="selected" @endif value="20:15">20:15</option>
            <option @if("20:20" == $service->duration) selected="selected" @endif value="20:20">20:20</option>
            <option @if("20:25" == $service->duration) selected="selected" @endif value="20:25">20:25</option>
            <option @if("20:30" == $service->duration) selected="selected" @endif value="20:30">20:30</option>
            <option @if("20:35" == $service->duration) selected="selected" @endif value="20:35">20:35</option>
            <option @if("20:40" == $service->duration) selected="selected" @endif value="20:40">20:40</option>
            <option @if("20:45" == $service->duration) selected="selected" @endif value="20:45">20:45</option>
            <option @if("20:50" == $service->duration) selected="selected" @endif value="20:50">20:50</option>
            <option @if("20:55" == $service->duration) selected="selected" @endif value="20:55">20:55</option>
            <option @if("21:00" == $service->duration) selected="selected" @endif value="21:00">21:00</option>
            <option @if("21:05" == $service->duration) selected="selected" @endif value="21:05">21:05</option>
            <option @if("21:10" == $service->duration) selected="selected" @endif value="21:10">21:10</option>
            <option @if("21:15" == $service->duration) selected="selected" @endif value="21:15">21:15</option>
            <option @if("21:20" == $service->duration) selected="selected" @endif value="21:20">21:20</option>
            <option @if("21:25" == $service->duration) selected="selected" @endif value="21:25">21:25</option>
            <option @if("21:30" == $service->duration) selected="selected" @endif value="21:30">21:30</option>
            <option @if("21:35" == $service->duration) selected="selected" @endif value="21:35">21:35</option>
            <option @if("21:40" == $service->duration) selected="selected" @endif value="21:40">21:40</option>
            <option @if("21:45" == $service->duration) selected="selected" @endif value="21:45">21:45</option>
            <option @if("21:50" == $service->duration) selected="selected" @endif value="21:50">21:50</option>
            <option @if("21:55" == $service->duration) selected="selected" @endif value="21:55">21:55</option>
            <option @if("22:00" == $service->duration) selected="selected" @endif value="22:00">22:00</option>
            <option @if("22:05" == $service->duration) selected="selected" @endif value="22:05">22:05</option>
            <option @if("22:10" == $service->duration) selected="selected" @endif value="22:10">22:10</option>
            <option @if("22:15" == $service->duration) selected="selected" @endif value="22:15">22:15</option>
            <option @if("22:20" == $service->duration) selected="selected" @endif value="22:20">22:20</option>
            <option @if("22:25" == $service->duration) selected="selected" @endif value="22:25">22:25</option>
            <option @if("22:30" == $service->duration) selected="selected" @endif value="22:30">22:30</option>
            <option @if("22:35" == $service->duration) selected="selected" @endif value="22:35">22:35</option>
            <option @if("22:40" == $service->duration) selected="selected" @endif value="22:40">22:40</option>
            <option @if("22:45" == $service->duration) selected="selected" @endif value="22:45">22:45</option>
            <option @if("22:50" == $service->duration) selected="selected" @endif value="22:50">22:50</option>
            <option @if("22:55" == $service->duration) selected="selected" @endif value="22:55">22:55</option>
            <option @if("23:00" == $service->duration) selected="selected" @endif value="23:00">23:00</option>
            <option @if("23:05" == $service->duration) selected="selected" @endif value="23:05">23:05</option>
            <option @if("23:10" == $service->duration) selected="selected" @endif value="23:10">23:10</option>
            <option @if("23:15" == $service->duration) selected="selected" @endif value="23:15">23:15</option>
            <option @if("23:20" == $service->duration) selected="selected" @endif value="23:20">23:20</option>
            <option @if("23:25" == $service->duration) selected="selected" @endif value="23:25">23:25</option>
            <option @if("23:30" == $service->duration) selected="selected" @endif value="23:30">23:30</option>
            <option @if("23:35" == $service->duration) selected="selected" @endif value="23:35">23:35</option>
            <option @if("23:40" == $service->duration) selected="selected" @endif value="23:40">23:40</option>
            <option @if("23:45" == $service->duration) selected="selected" @endif value="23:45">23:45</option>
            <option @if("23:50" == $service->duration) selected="selected" @endif value="23:50">23:50</option>
            <option @if("23:55" == $service->duration) selected="selected" @endif value="23:55">23:55</option>
        </select>
        @if($errors->has('duration'))
            <p class="help-block">
                {{ $errors->first('duration') }}
            </p>
        @endif
    </div>

    <div class="col-md-6 form-group ">
        {!! Form::label('color', 'Color*', ['class' => 'control-label']) !!}
        {!! Form::color('color', old('color'), ['id' => 'color', 'class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>


<div class="row">
    <div class="form-group col-md-4">
        {!! Form::label('end_node', 'End Node?*', ['class' => 'control-label']) !!}<br/>
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox('end_node', 1, old('end_node'), ['id' => 'end_node', 'placeholder' => '']) !!} End Node
            <span></span>
            @if($errors->has('end_node'))
                <p class="help-block">
                    {{ $errors->first('end_node') }}
                </p>
            @endif
        </label>
    </div>
    <div class="form-group col-md-4 end_node">
        {!! Form::label('complimentory', 'Complimentary?*', ['class' => 'control-label']) !!}<br/>
        <label class="mt-checkbox mt-checkbox-outline">
            {!! Form::checkbox('complimentory', 1, old('complimentory'), ['id' => 'complimentory', 'placeholder' => '']) !!} Complimentory
            <span></span>
            @if($errors->has('complimentory'))
                <p class="help-block">
                    {{ $errors->first('complimentory') }}
                </p>
            @endif
        </label>
    </div>
    <div class="form-group col-md-4 is_mobile">
        {!! Form::label('is_mobile', 'Is show on mobile?', ['class' => 'control-label']) !!}
        <br/>
        <label class="mt-checkbox">
            {!! Form::checkbox('is_mobile', 1, old('is_mobile')) !!} Is Mobile Active
            <span></span>
        </label>
        @if($errors->has('is_mobile'))
            <p class="help-block">
                {{ $errors->first('is_mobile') }}
            </p>
        @endif
    </div>
    <div class="col-md-4 form-group">
        {!! Form::label('price', 'Price*', ['class' => 'control-label']) !!}
        {!! Form::number('price', old('price'), ['min' => 0, 'step' => 0.01, 'id' => 'price', 'class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('name'))
            <p class="help-block">
                {{ $errors->first('name') }}
            </p>
        @endif
    </div>
</div>
<div class="row">
    <div class="form-group col-md-4">
        <label>Tax</label>
        <div class="mt-radio-list">
            @foreach($tax_treatment_types as $tax_treatment_type)
                <label class="mt-radio">{{$tax_treatment_type->name}}
                    <input type="radio" value="{{$tax_treatment_type->id}}" name="tax_treatment_type_id" {{ ($tax_treatment_type->id == $select_tax_treatment_type)? "checked" : "" }}/>
                    <span></span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="form-group col-md-4 consultancy_type">
        <label>Consultancy Type</label>
        <div class="mt-radio-list">
            @foreach($consultancy_types as $consultancy_type)
                <label class="mt-radio">{{$consultancy_type['name']}}
                    <input type="radio" value="{{$consultancy_type['id']}}" name="consultancy_type" {{ ($consultancy_type['id'] == $select_consultancy_type)? "checked" : "" }}/>
                    <span></span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('Description', 'description', ['class' => 'control-label']) !!}
        {!! Form::text('description', old('description'), ['class' => 'form-control', 'placeholder' => '']) !!}
        @if($errors->has('description'))
            <p class="help-block">
                {{ $errors->first('description') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-4">
        {!! Form::label('Category', 'Category', ['class' => 'control-label']) !!}
        {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2 category-select', 'required' => 'required']) !!}
    </div>
</div>
<div class="row">
    <div class="col-md-4 form-group">
        {!! Form::label('Select Image', 'Select Image', ['class' => 'control-label']) !!}
        <br>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;">
                <img src="{{asset('service_images/')}}/{{$service->image_src}}" alt=""/>
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
