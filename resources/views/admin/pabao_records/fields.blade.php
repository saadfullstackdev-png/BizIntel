<input type="hidden" name="id" value="{{$id}}">
<table class="table table-striped">
    <tbody>
    <tr>
        <th>Total</th>
        <td>{{ number_format($totol_amount,2) }}</td>
    </tr>
    <tr>
        <th>Paid Amount</th>
        <td>{{ number_format($paid_amount,2) }}</td>
    </tr>
    <tr>
        <th>Outstanding</th>
        <td>{{ number_format($outstanding_amount,2) }}</td>
    </tr>
    </tbody>
</table>
<div class="row">
    <div class="form-group col-md-6">
        {!! Form::label('date', 'Date*', ['class' => 'control-label']) !!}
        {!! Form::text('date_paid',null, ['readonly' => true, 'id' => 'date_paid', 'class' => 'form-control date_paid', 'placeholder' => '']) !!}
        @if($errors->has('date_paid'))
            <p class="help-block">
                {{ $errors->first('date_paid') }}
            </p>
        @endif
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('amount', 'Amount Received*', ['class' => 'control-label']) !!}
        <input type="number" name="amount" id="amount" class="form-control" max="{{$outstanding_amount}}">
    @if($errors->has('amount_received'))
            <p class="help-block">
    {{ $errors->first('amount_received') }}
    </p>
        @endif
    </div>
</div>
<div class="clearfix"></div>