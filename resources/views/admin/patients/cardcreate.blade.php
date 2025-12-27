@extends('layouts.app')
@section('stylesheets')
<!-- BEGIN PAGE LEVEL PLUGINS -->
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}"
    rel="stylesheet" type="text/css" />
<!-- END PAGE LEVEL PLUGINS -->

<link href="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}"
    rel="stylesheet" type="text/css" />
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
    rel="stylesheet" type="text/css" />

<link href="{{ url('metronic/assets/global/css/override.css') }}" rel="stylesheet" type="text/css" />

<style type="text/css">
    #service_id span.select2-container {
        z-index: 10050;
    }
</style>
@stop

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1>Add New Card Subscription</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.card-subscription.store') }}" method="POST">
                @csrf
                {{-- <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" name="card_number" id="card_number" class="form-control" value="{{ old('card_number') }}" required>
                </div> --}}

                <div class="form-group">
                    <label for="patient_id">Patient</label>
                    <select name="patient_id" id="patient_id" class="form-control patient_id" required>
                        <option value="" disabled selected>Select Patient</option>
                    </select>
                </div>        
                

                {{-- <div class="form-group">
                    <label for="patient_id">Patient ID</label>
                    <input type="number" name="patient_id" id="patient_id" class="form-control" value="{{ old('patient_id') }}" required>
                </div> --}}
                {{-- <div class="form-group col-sm-3 sn-select @if($errors->has('patient_id')) has-error @endif"
                    id="patient_id_E">
                    {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                    <select name="patient_id" id="patient_id" class="form-control patient_id" style="width: 100%;"></select>
                    <span id="patient_id_handler"></span>
                </div> --}}

                {{-- <div class="form-group">
                    <label for="account_id">Account ID</label>
                    <input type="number" name="account_id" id="account_id" class="form-control" value="{{ old('account_id') }}" required>
                </div> --}}

                {{-- <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" id="is_active" class="form-control" required>
                        <option value="1" {{ old('is_active', $subscription->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $subscription->is_active ?? 1) == 0 ? 'selected' : '' }}>Non-Active</option>
                    </select>
                </div>                 --}}

                {{-- <div class="form-group">
                    <label for="subscription_date">Subscription Date</label>
                    <input type="date" name="subscription_date" id="subscription_date" class="form-control" value="{{ old('subscription_date') }}" required>
                </div> --}}

                {{-- <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ old('expiry_date') }}" required>
                </div> --}}

                <button type="submit" class="btn btn-success">Apply</button>
                <a href="{{ route('admin.card-subscription.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascript')
<script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
    type="text/javascript"></script>
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
    type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('metronic/assets/global/scripts/app.min.js') }}" type="text/javascript"></script>
<script src="{{ url('metronic/assets/pages/scripts/components-date-time-pickers.min.js') }}"
    type="text/javascript"></script>
<script src="{{ url('js/admin/reports/summary/general.js') }}" type="text/javascript"></script>
<script src="{{ url('js/admin/users/ajaxbaseselect2.js') }}" type="text/javascript"></script>
@endsection
