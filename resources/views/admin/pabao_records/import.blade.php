@extends('layouts.app')
<link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
      type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
      type="text/css"/>
<link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
      rel="stylesheet" type="text/css"/>
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.pabao_records.title')</h1>
    <!-- END PAGE TITLE-->
@endsection
@section('content')
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-plus font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.pabao_records.import')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.pabao_records.index') }}" class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::open(['method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'form-validation', 'route' => ['admin.pabao_records.upload']]) !!}
                    <div class="form-body">
                        <!-- Starts Form Validation Messages -->
                        @include('partials.messages')
                        <!-- Ends Form Validation Messages -->

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 form-group">
                                {!! Form::label('location_id', 'Centre*', ['class' => 'control-label']) !!}
                                {!! Form::select('location_id', $locations, null, ['id' => 'location_id', 'class' => 'form-control select2', 'placeholder' => '', 'required' => '']) !!}
                                @if($errors->has('location_id'))
                                    <p class="help-block">
                                        {{ $errors->first('location_id') }}
                                    </p>
                                @endif
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 form-group">
                                {!! Form::label('pabao_records_file', 'File*', ['class' => 'control-label']) !!}
                                {!! Form::file('pabao_records_file', old('pabao_records_file'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                                @if($errors->has('pabao_records_file'))
                                    <p class="help-block">
                                        {{ $errors->first('pabao_records_file') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        {!! Form::submit('Upload', ['class' => 'btn btn-success']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}" type="text/javascript"></script>
    <script src="{{ url('js/admin/pabao_records/import.js') }}" type="text/javascript"></script>
@endsection

