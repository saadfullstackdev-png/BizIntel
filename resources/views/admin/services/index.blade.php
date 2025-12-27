@inject('request', 'Illuminate\Http\Request')
@inject('Auth','Auth' )
@inject('filters', 'App\Helpers\Filters')
@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}"
          rel="stylesheet" type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css') }}"
          rel="stylesheet" type="text/css"/>

    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/select2/css/select2-bootstrap.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css') }}" rel="stylesheet" type="text/css" />
    <!-- END PAGE LEVEL PLUGINS -->
@stop

@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.services.title')</h1>
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
            <div class="actions">
                @if(Gate::allows('services_create'))
                    <a class="btn btn-success" href="{{ route('admin.services.create') }}" data-target="#ajax_services" data-toggle="modal">@lang('global.app_add_new')</a>
                @endif
            </div>
        </div>
        <div class="portlet-body">
            <div class="table-container">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Color</th>
                        <th>Price</th>
                        <th>Complimentory</th>
                        <th>Is Mobile</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        {!! Form::open(['route' => 'admin.services.index', 'method' => 'get']) !!}
                        <th>
                            <select name="status" class="form-control form-filter input-sm select2 select2-hidden-accessible" id="status">
                                <option value="2">All</option>
                                <option value="0">Inactive</option>
                                <option value="1">Active</option>
                            </select>
                        </th>
                        <th>
                            {!! Form::button('<i class="fa fa-search"></i> Search', ['type' => 'submit','class' => 'btn btn-sm green btn-outline filter-submit margin-bottom']) !!}
                        </th>
                        {!! Form::close() !!}
                    </tr>
                    </thead>
                    <tbody>
                    @if (count($Services) > 0)
                        @foreach ($Services as $id => $data)
                            @if ( $id != 0 )
                                @if ($request->status == $data['active'] || $request->status == 2 || $id < 0 || is_null($request->status) )
                                    @if ($id == 0) @continue; @endif
                                    <tr>
                                        <td>
                                            @if ($id < 0)
                                                <b>{{ $data['id'] }}</b>
                                            @else
                                                @if ($request->status == $data['active'] || $request->status == 2 || is_null($request->status))
                                                    {{ $data['id'] }}
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($id < 0)
                                                <b>{!! $data['name'] !!}</b>
                                            @else
                                                @if ($request->status == $data['active'] || $request->status == 2 || is_null($request->status))
                                                    {!! $data['name'] !!}
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{ $data['duration'] . ' mins' }}</td>
                                        <td><span class="btn btn-xs" style="background-color: {{ $data['color'] }} !important; color: lightgray">{{ $data['color'] }}</span>
                                        </td>
                                        @if($data['name'] == 'All Services')
                                            <td>{{'-'}}</td>
                                            <td>{{'-'}}</td>
                                        @else
                                            <td>{{ number_format($data['price'], 2) }}</td>
                                            <td>@if ($id < 0) - @else {{ $data['complimentory'] == '1'?'Yes':'NO'}} @endif</td>
                                        @endif
                                        @if($data['end_node'] == 0)
                                            <td>{{ $data['is_mobile'] == '1' ? 'Yes':'NO'}}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{ $data['category_name'] ?? '-' }}</td>
                                        <td>
                                            @if($data['active'])
                                                @if(Gate::allows('services_inactive'))
                                                    {!! Form::open(array(
                                                    'style' => 'display: inline-block;',
                                                    'method' => 'PATCH',
                                                    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                                    'route' => ['admin.services.inactive', $data['id']])) !!}
                                                    {!! Form::submit(trans('global.app_inactive'), array('class' => 'btn btn-xs btn-warning')) !!}
                                                    {!! Form::close() !!}
                                                @endif
                                            @else
                                                @if(Gate::allows('services_active'))
                                                    {!! Form::open(array(
                                                    'style' => 'display: inline-block;',
                                                    'method' => 'PATCH',
                                                    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                                    'route' => ['admin.services.active', $data['id']])) !!}
                                                    {!! Form::submit(trans('global.app_active'), array('class' => 'btn btn-xs btn-primary')) !!}
                                                    {!! Form::close() !!}
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if(Gate::allows('services_edit'))
                                                <a class="btn btn-xs btn-info" href="{{ route('admin.services.edit',[$data['id']]) }}" data-target="#ajax_services" data-toggle="modal">@lang('global.app_edit')</a>
                                            @endif
                                            @if(Gate::allows('services_destroy'))
                                                {!! Form::open(array(
                                                    'style' => 'display: inline-block;',
                                                    'method' => 'DELETE',
                                                    'onsubmit' => "return confirm('".trans("global.app_are_you_sure")."');",
                                                    'route' => ['admin.services.destroy', $data['id']])) !!}
                                                {!! Form::submit(trans('global.app_delete'), array('class' => 'btn btn-xs btn-danger')) !!}
                                                {!! Form::close() !!}
                                            @endif
                                        </td>
                                    </tr>

                                @endif
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3">No entires found.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable 1 -->
    <!--Edit View model Start-->
    <div class="modal fade" id="ajax_services" role="basic" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-body">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
    <!--Edit View model End-->
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/datatables/datatables.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/select2/js/select2.full.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/pages/scripts/components-select2.min.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js') }}" type="text/javascript"></script>
@endsection