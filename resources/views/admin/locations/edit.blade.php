<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">@lang('global.app_edit')</h4>
</div>
<div class="modal-body">
    <div class="portlet-body form">
        <div class="form-group">
            {!! Form::model($location, ['method' => 'PUT', 'id' => 'form-validation','enctype' => 'multipart/form-data', 'route' => ['admin.locations.updatelocation', $location->id]]) !!}
            <div class="form-body">
                <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->

                @include('admin.locations.fields_update')
            </div>
            <div>
                {!! Form::submit(trans('global.app_update'), ['class' => 'btn btn-success']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
<script src="{{ url('js/admin/locations/fields_update.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#services").select2({
            width: "100%"
        });
    });
</script>