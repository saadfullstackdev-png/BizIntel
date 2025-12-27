@extends('layouts.app')

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.discountallocations.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-plus font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.discountallocations.import')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.discountallocations.index') }}" class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body form">
            <div class="form-group">
                {!! Form::open(['method' => 'POST', 'enctype' => 'multipart/form-data', 'id' => 'form-validation', 'route' => ['admin.discountallocations.upload']]) !!}
                <div class="form-body">
                    <!-- Starts Form Validation Messages -->
                @include('partials.messages')
                <!-- Ends Form Validation Messages -->

                    <div class="form-group">
                        {!! Form::label('discountallocation_file', 'File*', ['class' => 'control-label']) !!}
                        {!! Form::file('discountallocation_file', old('discountallocation_file'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                        <p class="help-block">To download sample file <a href="{{ url('discountAllocationSample.xlsx') }}" target="_blank">click here</a> .</p>
                        @if($errors->has('discountallocation_file'))
                            <p class="help-block">
                                {{ $errors->first('discountallocation_file') }}
                            </p>
                        @endif
                    </div>
                    <div class="form-group">
                        <div class="mt-checkbox-inline">
                            <label class="mt-checkbox mt-checkbox-outline"> Is celebrity
                                <input type="checkbox" value="1" id="is_celebrity" name="is_celebrity">
                                <span></span>
                            </label>
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
    <script src="{{ url('js/admin/discountallocations/import.js') }}" type="text/javascript"></script>
@endsection

