    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">@lang('global.app_edit')</h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::model($resource_types, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.resource_types.update', $resource_types->id]]) !!}
                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->

                    @include('admin.resource_types.fields')
                </div>
                <div>
                    {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <script src="{{ url('js/admin/resource_types/fields.js') }}" type="text/javascript"></script>
</div>

