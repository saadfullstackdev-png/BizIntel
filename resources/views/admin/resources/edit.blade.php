<link href="{{ url('metronic/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css') }}" rel="stylesheet"
      type="text/css"/>
<link href="{{ url('metronic/assets/global/css/components.min.css') }}" rel="stylesheet" id="style_components"
      type="text/css"/>
<link href="{{ url('metronic/assets/global/css/plugins.min.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css') }}"
      rel="stylesheet" type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/css/bootstrap-multiselect.css') }}"
      rel="stylesheet" type="text/css"/>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_edit')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($resource, ['method' => 'PUT', 'id' => 'form-validation', 'route' => ['admin.resources.update', $resource->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
            @include('partials.messages')
            <!-- Ends Form Validation Messages -->

                @include('admin.resources.edit_fields')
            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
    <script src="{{ url('js/admin/resources/fields.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/resources/update.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-multiselect/js/bootstrap-multiselect.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/users/component-multiselect.js') }}" type="text/javascript"></script>
</div>
