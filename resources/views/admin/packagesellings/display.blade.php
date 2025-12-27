<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Name</th>
            <td>{{ $package->name }}</td>
            <th>Paid Amount</th>
            <td>{{ number_format($package->tax_including_price, 2) }}</td>
        </tr>
        <tr>
            <th>Total Services</th>
            <td>{{ $package->total_services }}</td>
        </tr>
        </tbody>
    </table>

    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Service</th>
            <th>Is Consumed</th>
            <th>Price</th>
        </tr>
        </tbody>
        <tbody>
        @if($records)
            @foreach($records as $record)
                <tr>
                    <td>{{ $record['service'] }}</td>
                    <td>{{ $record['is_consumed'] }}</td>
                    <td>{{ number_format($record['tax_including_price'], 2) }}</td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>