<div class="modal-header">
    <button type="button" id="closeBtn" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Appointment Notification Logs</h4>
</div>
<div class="modal-body">
    <div class="table-responsive">
        <table id="table" class="table table-striped table-bordered table-advance table-hover">
            {{ csrf_field() }}
            <thead>
            <tr>
                <th>Phone</th>
                <th>Text</th>
                <th>Sent</th>
                <th>Is_Refund</th>
                <th>Type</th>
                <th width="20%">Created at</th>
            </tr>
            </thead>
            @if(count($notificationLogs))
                @foreach($notificationLogs as $notificationLog)
                    @if(is_null($notificationLog->invoice_id))
                        <tr>
                            <td>{{ \App\Helpers\GeneralFunctions::prepareNumber4Call(\App\Helpers\GeneralFunctions::cleanNumber($notificationLog->to)) }}</td>
                            <td><a href="javascript:void(0);"
                                   onclick="toggle({{$notificationLog->id}})">{{ substr($notificationLog->text, 0, 50) . '...' }}</a>
                            </td>
                            <td id="smsRow{{ $notificationLog->id }}">
                                @if($notificationLog->status)
                                    {{ 'Yes' }}
                                @else
                                    <span id="spanRow{{ $notificationLog->id }}">{{ 'No' }}</span>
                                    <br/><a id="clickRow{{ $notificationLog->id }}" href="javascript:void(0)"
                                            onclick="resendNotification('{{ $notificationLog->id }}');" class="btn btn-xs btn-success"
                                            data-toggle="tooltip" title="Resend notification"><i class="fa fa-send"></i></a>
                                @endif
                            </td>
                            @if($notificationLog->is_refund == "Yes")
                                <td>{{$notificationLog->is_refund}}</td>
                                <td></td>
                            @else
                                <td></td>
                                <td>@if(array_key_exists($notificationLog->log_type, Config::get('constants.sms_array'))){{ Config::get('constants.sms_array')[$notificationLog->log_type] }}@else{{ 'N/A' }}@endif</td>
                            @endif
                            <td>{{ \Carbon\Carbon::parse($notificationLog->created_at)->format('M d,Y h:i A') }}</td>
                        </tr>
                        @foreach ($notificationLogs as $notificationlogsdetail)
                            @if($notificationlogsdetail->id == $notificationLog->id )
                                <tr class="{{$notificationLog->id}}" style="display: none">
                                    <td colspan="5">
                                        <pre>{{$notificationLog->text}}</pre>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @else
                <tr>
                    <td colspan="4">No Notification log found.</td>
                </tr>
            @endif
        </table>
    </div>
</div>
<div class="modal-footer" id="modal-footer">
    <button type="button" class="btn default" data-dismiss="modal">Close</button>
</div>
<script>
    function toggle(id) {
        $("." + id).toggle();
    }
</script>
<script type="text/javascript">
    function resendNotification(smsId) {
        $('#clickRow' + smsId).hide();
        $('#spanRow' + smsId).text('Wait');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.appointments.resend_notification'),
            type: "PUT",
            data: {
                id: smsId
            },
            cache: false,
            success: function (response) {
                if (response.status == '1') {
                    $('#smsRow' + smsId).text('Yes');
                } else {
                    $('#clickRow' + smsId).show();
                    $('#spanRow' + smsId).text('No');
                }
            }
        });
    }
</script>