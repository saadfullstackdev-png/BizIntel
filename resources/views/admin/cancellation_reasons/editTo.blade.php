    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_edit')</h4>
    </div>
    <div class="modal-body">
    <div class="form-group">
        {!! Form::model($cancellation_reason, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.cancellation_reasons.update', $cancellation_reason->id]]) !!}
        <div class="form-body">
            <!-- Starts Form Validation Messages -->
        @include('partials.messages')
        <!-- Ends Form Validation Messages -->

            @include('admin.cancellation_reasons.fields')
        </div>
        <div>
            {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
        </div>
        {!! Form::close() !!}
    </div>
        <script src="{{ url('js/admin/cancellation_reasons/fields.js') }}" type="text/javascript"></script>
</div>
