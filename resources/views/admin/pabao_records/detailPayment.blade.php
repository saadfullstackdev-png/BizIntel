<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_detail')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet-body">
                        <div class="table-scrollable">
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
                        </div>
                    </div>
                </div>
            </div>
            <h3>Payment</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet-body">
                        <div class="table-scrollable">
                            <table class="table table-striped">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                                @if(count($payment_pabau_history)>0)
                                    @foreach($payment_pabau_history as $pay)
                                        <tr>
                                            <td>{{\Carbon\Carbon::parse($pay->date_paid)->format('F j,Y')}}</td>
                                            <td>{{number_format($pay->amount,2)}}</td>
                                            @if(Gate::allows('pabao_records_destroy'))
                                                <td>
                                                    {!! Form::open(array(
                                                    'style' => 'display: inline-block;',
                                                    'method' => 'POST',
                                                    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                                    'route' => ['admin.pabao_records.delete_record', $pay->id])) !!}
                                                    {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                                    {!! Form::close() !!}

                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            </table>
                        </div>
                        <a class="btn btn-lg blue hidden-print margin-bottom-5 pull-right" target="_blank"
                           href="{{ route('admin.pabao_records.pabao_pdf',[$pabao_payment->id]) }}">@lang('global.app_pdf')
                            <i class="fa fa-print"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>