<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Name</th>
            <td>{{ $bundle->name }}</td>
            <th>Offered Price</th>
            <td>{{ number_format($bundle->price, 2) }}</td>
        </tr>
        <tr>
            <th>Services Price</th>
            <td>{{ number_format($bundle->services_price, 2) }}</td>
            <th>Total Services</th>
            <td>{{ $bundle->total_services }}</td>
        </tr>
        </tbody>
    </table>

    <table class="table table-striped">
        <tbody>
        <tr>
            <th>Service</th>
            <th>Price</th>
        </tr>
        </tbody>
        <tbody>
            @if($relationships)
                @foreach($relationships as $relationship)
                    @if(array_key_exists($relationship->service_id, $bundle_services))
                        <tr>
                            <td>{{ $bundle_services[$relationship->service_id]->name }}</td>
                            <td>{{ number_format($relationship->service_price, 2) }}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
        </tbody>
    </table>
</div>