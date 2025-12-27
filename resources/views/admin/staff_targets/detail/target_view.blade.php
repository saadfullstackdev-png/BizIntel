<style>
    .has-y-scroll {
        min-height: inherit;
        max-height: 500px;
        overflow-y: auto;
    }
</style>
<div class="modal-body">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_detail')</h4>
    </div>
    <table class="table table-striped">
        <tbody>
        <tr>
            <th width="20%">Month</th>
            <td>{{ $staff_target->month }}</td>
            <th width="20%">Year</th>
            <td>{{ $staff_target->year }}</td>
        </tr>
        <tr>
            <th>Staff Name</th>
            <td colspan="3">{{ $staff_target->staff->name . ' - ' . $staff_target->staff->email  }}</td>
        </tr>
        {{--<tr>--}}
        {{--<th>Target Amount</th>--}}
        {{--<td>{{ number_format($staff_target->total_amount, 2) }}</td>--}}
        {{--<th>Target Services</th>--}}
        {{--<td>{{ number_format($staff_target->total_services) }}</td>--}}
        {{--</tr>--}}
        </tbody>
    </table>
    <div class="has-y-scroll">
        <table class="table table-striped table-bordered table-advance table-hover ">
            <thead>
            <tr>
                <th>Sr#</th>
                <th>Service Name</th>
                <th>Target Amount</th>
                <th>Target Services</th>
            </tr>
            </thead>
            <tbody>
            @if($staff_target->staff_target_services)
                @php($count = 1)
                @foreach($staff_target->staff_target_services as $staff_target_service)
                    <tr>
                        <td>{{ $count }}</td>
                        <td>{{ $staff_target_service->service->name }}</td>
                        <td align="right">{{ number_format($staff_target_service->target_amount, 2) }}</td>
                        <td align="right">{{ number_format($staff_target_service->target_services) }}</td>
                    </tr>
                    @php($count = $count + 1)
                @endforeach
                <tr>
                    <td colspan="2" align="right"><b>Grand Total</b></td>
                    <td align="right">{{ number_format($staff_target->total_amount, 2) }}</td>
                    <td align="right">{{ number_format($staff_target->total_services) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="3">
                        No record found.
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>