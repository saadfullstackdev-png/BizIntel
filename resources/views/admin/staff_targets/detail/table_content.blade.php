<table class="table table-striped table-bordered table-advance table-hover">
    <thead>
    <tr>
        <th>Service Name</th>
        <th>Target Amount</th>
        <th>Target Services</th>
    </tr>
    </thead>
    <tbody>
        @if(isset($staffTargetServices['target_services']) && count($staffTargetServices['target_services']))
            @foreach($staffTargetServices['target_services'] as $staffTargetService)
                <tr>
                    <td>{{ $staffTargetService['name'] }}</td>
                    <td>
                        {!! Form::number('target_amount[' . $staffTargetService['id'] . ']', $staffTargetService['target_amount'], ['id' => 'target_amount' . $staffTargetService['id'], 'onkeyup' => 'CreateFormValidation.calculateTargetAmount();', 'onblur' => 'CreateFormValidation.calculateTargetAmount();', 'min' => '0', 'class' => 'form-control target_amount']) !!}
                    </td>
                    <td>
                        {!! Form::number('target_services[' . $staffTargetService['id'] . ']', $staffTargetService['target_services'], ['id' => 'target_services' . $staffTargetService['id'], 'onkeyup' => 'CreateFormValidation.calculateTargetServices();', 'onblur' => 'CreateFormValidation.calculateTargetServices();', 'min' => '0', 'class' => 'form-control target_services']) !!}
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>