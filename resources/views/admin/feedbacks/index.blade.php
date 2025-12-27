@inject('request', 'Illuminate\Http\Request')
@inject('Auth', 'Auth')
@inject('filters', 'App\Helpers\Filters')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'}}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->
    <style type="text/css">
        .city span.select2-container {
            z-index: 10050;
        }
    </style>
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.feedbacks.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <!-- Begin: Demo Datatable 1 -->
    <div class="portlet light portlet-fit portlet-datatable bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-list font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">@lang('global.app_list')</span>
            </div>
            {{--            <div class="actions">--}}
            {{--                @if(Gate::allows('feedbacks_create'))--}}
            {{--                    <a class="btn btn-success btn-to-focus" href="{{ route('admin.feedbacks.create') }}"--}}
            {{--                       data-target="#ajax_feedbacks" data-toggle="modal">@lang('global.app_add_new')</a>--}}
            {{--                @endif--}}
            {{--            </div>--}}
        </div>
        <div class="portlet-body">
            <div class="table-container">
                @if(Gate::allows('feedbacks_destroy'))
                    <div class="table-actions-wrapper">
                        <span> </span>
                        <select class="table-group-action-input form-control input-inline input-small input-sm">
                            <option value="">Select</option>
                            <option value="Delete">Delete</option>
                        </select>
                        <button class="btn btn-sm red table-group-action-submit">
                            <i class="fa fa-check"></i> Submit
                        </button>
                    </div>
                @endif
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax">
                    <thead>
                    <tr role="row" class="heading">
                        <th width="3%">
                            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                <input type="checkbox" class="group-checkable" data-set="#sample_2 .checkboxes"/>
                                <span></span>
                            </label>
                        </th>
                        <th>@lang('global.feedbacks.fields.name')</th>
                        <th>@lang('global.feedbacks.fields.email')</th>
                        <th>@lang('global.feedbacks.fields.phone')</th>
                        <th>@lang('global.feedbacks.fields.subject')</th>
                        <th>@lang('global.feedbacks.fields.message')</th>
                        <th>@lang('global.feedbacks.fields.type')</th>
                        <th>@lang('global.feedbacks.fields.date')</th>
                        <th width="20%">@lang('global.feedbacks.fields.actions')</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'email') }}"
                                   name="email"></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'phone') }}"
                                   name="phone"></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'subject') }}"
                                   name="subject"></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'message') }}"
                                   name="message"></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'type') }}"
                                   name="type"></td>
                        <td><input type="text" class="form-control form-filter input-sm"
                                   value="{{ $filters->get(Auth::User()->id, 'feedbacks', 'created_at') }}"
                                   name="created_at"></td>
                        <td>
                            <div class="margin-bottom-5">
                                <button class="btn btn-sm green btn-outline filter-submit margin-bottom">
                                    <i class="fa fa-search"></i> Search
                                </button>
                                <button class="btn btn-sm red btn-outline filter-cancel reset_custom">
                                    <i class="fa fa-times"></i> Reset
                                </button>
                            </div>
                        </td>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@section('javascript')
    <script>
        $(".reset_custom").click(function () {
            $('.select2').val(null).trigger('change');
        });
    </script>
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ url('js/admin/feedbacks/datatable.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
            type="text/javascript"></script>
    <script src="{{'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'}}"
            type="text/javascript"></script>

@endsection
