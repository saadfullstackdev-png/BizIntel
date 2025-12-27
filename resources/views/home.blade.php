@extends('layouts.app')

@section('stylesheets')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="{{ url('metronic/assets/global/plugins/bootstrap-table/bootstrap-table.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->


    {{-- button css start --}}
    <style>
        .hover-effect {
            border-color: #3598dc !important;
            color: #FFF !important;
            background-color: #3598dc !important;
        }

        .hover-effect:hover {
            background-color: #FFF !important;
            color: #3598dc !important;
            border-color: #3598dc !important;
        }

        .action-style {
            float: inline-end;
            padding: 10px;
            margin-right: 10px;
        }
    </style>
    {{-- button css end --}}
@stop

@section('content')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title"> Dashboard</h1>
    <!-- END PAGE TITLE-->

    <div class="row">

        @if(Gate::allows('dashboard_collection_by_centre'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_collection_by_centre">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">Collection by Centre</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','false','today']) }}"
                                                   target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','false','yesterday']) }}"
                                                   target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','false','last7days']) }}"
                                                   target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','false','thismonth']) }}"
                                                   target="_blank">This Month</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#location_collection_1" data-toggle="tab"
                                   onclick="Home.initCollectionByCentre('today', '', '', '');">Today</a>
                            </li>
                            <li>
                                <a href="#location_collection_2" data-toggle="tab"
                                   onclick="Home.initCollectionByCentre('', 'yesterday', '', '');">Yesterday</a>
                            </li>
                            <li>
                                <a href="#location_collection_3" data-toggle="tab"
                                   onclick="Home.initCollectionByCentre('', '', 'last7days', '');">Last 7 Days</a>
                            </li>
                            <li>
                                <a href="#location_collection_4" data-toggle="tab"
                                   onclick="Home.initCollectionByCentre('', '', '', 'thismonth');">This Month</a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="location_collection_1">
                                <div id="location_collection_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_collection_2">
                                <div id="location_collection_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_collection_3">
                                <div id="location_collection_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_collection_4">
                                <div id="location_collection_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_my_collection_by_centre'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_my_collection_by_centre">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">My Collection by Centre</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','true','today']) }}"
                                                   target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','true','yesterday']) }}"
                                                   target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','true','last7days']) }}"
                                                   target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.collectionrevenuereport',['web','true','thismonth']) }}"
                                                   target="_blank">This Month</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#location_my_collection_1" data-toggle="tab"
                                   onclick="Home.initMyCollectionByCentre('today', '', '', '');">Today</a>
                            </li>
                            <li>
                                <a href="#location_my_collection_2" data-toggle="tab"
                                   onclick="Home.initMyCollectionByCentre('', 'yesterday', '', '');">Yesterday</a>
                            </li>
                            <li>
                                <a href="#location_my_collection_3" data-toggle="tab"
                                   onclick="Home.initMyCollectionByCentre('', '', 'last7days', '');">Last 7 Days</a>
                            </li>
                            <li>
                                <a href="#location_my_collection_4" data-toggle="tab"
                                   onclick="Home.initMyCollectionByCentre('', '', '', 'thismonth');">This Month</a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="location_my_collection_1">
                                <div id="location_my_collection_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_my_collection_2">
                                <div id="location_my_collection_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_my_collection_3">
                                <div id="location_my_collection_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_my_collection_4">
                                <div id="location_my_collection_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_revenue_by_centre'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_revenue_by_centre">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">Revenue by Centre</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.revenue_by_centre',['today','web']) }}"
                                                   target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.revenue_by_centre',['yesterday','web']) }}"
                                                   target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.revenue_by_centre',['last7days','web']) }}"
                                                   target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.revenue_by_centre',['thismonth','web']) }}"
                                                   target="_blank">This Month</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#location_revenue_4" data-toggle="tab"
                                   onclick="Home.initRevenueByCentre('today');"> Today </a>
                            </li>
                            <li>
                                <a href="#location_revenue_1" data-toggle="tab"
                                   onclick="Home.initRevenueByCentre('yesterday');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#location_revenue_2" data-toggle="tab"
                                   onclick="Home.initRevenueByCentre('last7days');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#location_revenue_3" data-toggle="tab"
                                   onclick="Home.initRevenueByCentre('thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>

                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="location_revenue_4">
                                <div id="location_revenue_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_revenue_1">
                                <div id="location_revenue_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_revenue_2">
                                <div id="location_revenue_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="location_revenue_3">
                                <div id="location_revenue_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_my_revenue_by_centre'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_my_revenue_by_centre">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">My Revenue by Centre</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a target="_blank"
                                                   href="{{route('admin.dashboardreport.revenue_by_centre',['today','web',true])}}">Today</a>
                                            </li>
                                            <li>
                                                <a target="_blank"
                                                   href="{{ route('admin.dashboardreport.revenue_by_centre',['yesterday','web', true]) }}">Yesterday</a>
                                            </li>
                                            <li>
                                                <a target="_blank"
                                                   href="{{ route('admin.dashboardreport.revenue_by_centre',['last7days','web',true]) }}">Last
                                                    7 Days</a>
                                            </li>
                                            <li>
                                                <a target="_blank"
                                                   href="{{ route('admin.dashboardreport.revenue_by_centre',['thismonth', 'web', true]) }}">This
                                                    Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#my_location_revenue_4" data-toggle="tab"
                                   onclick="Home.initMyRevenueByCentre('today');"> Today </a>
                            </li>
                            <li>
                                <a href="#my_location_revenue_1" data-toggle="tab"
                                   onclick="Home.initMyRevenueByCentre('yesterday');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#my_location_revenue_2" data-toggle="tab"
                                   onclick="Home.initMyRevenueByCentre('last7days');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#my_location_revenue_3" data-toggle="tab"
                                   onclick="Home.initMyRevenueByCentre('thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="my_location_revenue_4">
                                <div id="my_location_revenue_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_location_revenue_1">
                                <div id="my_location_revenue_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_location_revenue_2">
                                <div id="my_location_revenue_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_location_revenue_3">
                                <div id="my_location_revenue_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_revenue_by_service'))
            <div class="col-lg-6 col-xs-12 col-sm-12"  id="revenue_by_service">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">Revenue by Service</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','false','today']) }}" target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','false','yesterday']) }}" target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','false','last7days']) }}" target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','false','thismonth']) }}" target="_blank">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#service_revenue_4" data-toggle="tab" onclick="Home.initRevenueByService('today', '', '', '');"> Today </a>
                            </li>
                            <li>
                                <a href="#service_revenue_1" data-toggle="tab" onclick="Home.initRevenueByService('', 'yesterday', '', '');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#service_revenue_2" data-toggle="tab" onclick="Home.initRevenueByService('', '', 'last7days', '');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#service_revenue_3" data-toggle="tab" onclick="Home.initRevenueByService('', '', '', 'thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="service_revenue_4">
                                <div id="service_revenue_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="service_revenue_1">
                                <div id="service_revenue_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="service_revenue_2">
                                <div id="service_revenue_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="service_revenue_3">
                                <div id="service_revenue_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_my_revenue_by_service'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="my_revenue_by_service">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">My Revenue by Service</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','true','today']) }}" target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','true','yesterday']) }}" target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','true','last7days']) }}" target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.revenuebyservicereport',['web','true','thismonth']) }}" target="_blank">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#my_service_revenue_4" data-toggle="tab" onclick="Home.initMyRevenueByService('today', '', '', '');"> Today </a>
                            </li>
                            <li>
                                <a href="#my_service_revenue_1" data-toggle="tab" onclick="Home.initMyRevenueByService('', 'yesterday', '', '');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#my_service_revenue_2" data-toggle="tab" onclick="Home.initMyRevenueByService('', '', 'last7days', '');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#my_service_revenue_3" data-toggle="tab" onclick="Home.initMyRevenueByService('', '', '', 'thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="my_service_revenue_4">
                                <div id="my_service_revenue_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_service_revenue_1">
                                <div id="my_service_revenue_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_service_revenue_2">
                                <div id="my_service_revenue_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_service_revenue_3">
                                <div id="my_service_revenue_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_appointment_by_status'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_appointment_by_status">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">Appointments by Status</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a target="_blank" href="{{ route('admin.dashboardreport.appointmentsByStatus',['today','web', 'false']) }}">Today</a>
                                            </li>
                                            <li>
                                                <a target="_blank" href="{{ route('admin.dashboardreport.appointmentsByStatus',['yesterday','web', 'false']) }}">Yesterday</a>
                                            </li>
                                            <li>
                                                <a target="_blank" href="{{ route('admin.dashboardreport.appointmentsByStatus',['last7days','web','false']) }}">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a target="_blank" href="{{ route('admin.dashboardreport.appointmentsByStatus',['thismonth','web','false']) }}">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#appointment_status_4" data-toggle="tab" onclick="Home.initAppointmentsByStatus('today');"> Today </a>
                            </li>
                            <li>
                                <a href="#appointment_status_1" data-toggle="tab" onclick="Home.initAppointmentsByStatus('yesterday');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#appointment_status_2" data-toggle="tab" onclick="Home.initAppointmentsByStatus('last7days');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#appointment_status_3" data-toggle="tab" onclick="Home.initAppointmentsByStatus('thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="appointment_status_4">
                                <div id="appointment_status_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_status_1">
                                <div id="appointment_status_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_status_2">
                                <div id="appointment_status_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_status_3">
                                <div id="appointment_status_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_my_appointment_by_status'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="dashboard_my_appointment_by_status">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">My Appointments by Status</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.appointmentsByStatus',['today', 'web', 'true' ]) }}" target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.appointmentsByStatus',['yesterday', 'web', 'true' ]) }}" target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.appointmentsByStatus',['last7days', 'web', 'true' ]) }}" target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboardreport.appointmentsByStatus',['thismonth', 'web', 'true' ]) }}" target="_blank">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#my_appointment_status_4" data-toggle="tab" onclick="Home.initMyAppointmentsByStatus('today');"> Today </a>
                            </li>
                            <li>
                                <a href="#my_appointment_status_1" data-toggle="tab" onclick="Home.initMyAppointmentsByStatus('yesterday');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#my_appointment_status_2" data-toggle="tab" onclick="Home.initMyAppointmentsByStatus('last7days');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#my_appointment_status_3" data-toggle="tab" onclick="Home.initMyAppointmentsByStatus('thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="my_appointment_status_4">
                                <div id="my_appointment_status_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_status_1">
                                <div id="my_appointment_status_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_status_2">
                                <div id="my_appointment_status_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_status_3">
                                <div id="my_appointment_status_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_appointment_by_type'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="appointment_by_type">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">Appointments by Type</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','false','today']) }}" target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','false','yesterday']) }}" target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','false','last7days']) }}" target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','false','thismonth']) }}" target="_blank">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#appointment_type_4" data-toggle="tab" onclick="Home.initAppointmentsByType('today', '', '', '');"> Today </a>
                            </li>
                            <li>
                                <a href="#appointment_type_1" data-toggle="tab" onclick="Home.initAppointmentsByType('', 'yesterday', '', '');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#appointment_type_2" data-toggle="tab" onclick="Home.initAppointmentsByType('', '', 'last7days', '');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#appointment_type_3" data-toggle="tab" onclick="Home.initAppointmentsByType('', '', '', 'thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="appointment_type_4">
                                <div id="appointment_type_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_type_1">
                                <div id="appointment_type_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_type_2">
                                <div id="appointment_type_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="appointment_type_3">
                                <div id="appointment_type_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(Gate::allows('dashboard_my_appointment_by_type'))
            <div class="col-lg-6 col-xs-12 col-sm-12" id="my_appointment_by_type">
                <div class="portlet light bordered">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <i class="icon-bubbles font-dark hide"></i>
                            <span class="caption-subject font-dark bold uppercase">My Appointments by Type</span>
                        </div>
                        <ul class="nav nav-tabs">
                            <li style="border-bottom: none;">
                                <div class="actions action-style">
                                    <div class="btn-group">
                                        <a class="btn blue btn-outline btn-circle btn-sm hover-effect"
                                           href="javascript:;" data-toggle="dropdown" data-hover="dropdown"
                                           data-close-others="true" aria-expanded="false"> Report
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','true','today']) }}" target="_blank">Today</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','true','yesterday']) }}" target="_blank">Yesterday</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','true','last7days']) }}" target="_blank">Last 7 Days</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.dashboadReport.appointmentbytype',['web','true','thismonth']) }}" target="_blank">This Month</a>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                            </li>
                            <li class="active">
                                <a href="#my_appointment_type_4" data-toggle="tab" onclick="Home.initMyAppointmentsByType('today', '', '', '');"> Today </a>
                            </li>
                            <li>
                                <a href="#my_appointment_type_1" data-toggle="tab" onclick="Home.initMyAppointmentsByType('', 'yesterday', '', '');"> Yesterday </a>
                            </li>
                            <li>
                                <a href="#my_appointment_type_2" data-toggle="tab" onclick="Home.initMyAppointmentsByType('', '', 'last7days', '');"> Last 7 Days </a>
                            </li>
                            <li>
                                <a href="#my_appointment_type_3" data-toggle="tab" onclick="Home.initMyAppointmentsByType('', '', '', 'thismonth');"> This Month </a>
                            </li>
                        </ul>
                    </div>
                    <div class="portlet-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="my_appointment_type_4">
                                <div id="my_appointment_type_today" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_type_1">
                                <div id="my_appointment_type_yesterday" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_type_2">
                                <div id="my_appointment_type_last7days" class="CSSAnimationChart"></div>
                            </div>
                            <div class="tab-pane" id="my_appointment_type_3">
                                <div id="my_appointment_type_thismonth" class="CSSAnimationChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="clearfix"></div>
    </div>
@stop

@section('javascript')
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/moment.min.js') }}" type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/amcharts/amcharts/amcharts.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/amcharts/amcharts/pie.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <script src="{{ url('metronic/assets/global/plugins/bootstrap-table/bootstrap-table.min.js') }}"
            type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="{{ url('js/home.js') }}" type="text/javascript"></script>
    <!-- END PAGE LEVEL SCRIPTS -->

    @if(Gate::allows('dashboard_revenue_by_service'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initRevenueByService('today','','','');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_revenue_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initRevenueByCentre('today');
            });
        </script>
    @endif
    @if(Gate::allows('dashboard_collection_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initCollectionByCentre('today', '', '', '');
            });
        </script>
    @endif
    @if(Gate::allows('dashboard_my_collection_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyCollectionByCentre('today', '', '', '');
            });
        </script>
    @endif
    @if(Gate::allows('dashboard_my_revenue_by_service'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyRevenueByService('today','','','');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_revenue_by_centre'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyRevenueByCentre('today');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_appointment_by_status'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initAppointmentsByStatus('today');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_appointment_by_type'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initAppointmentsByType('today','','','');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_appointment_by_status'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyAppointmentsByStatus('today');
            });
        </script>
    @endif

    @if(Gate::allows('dashboard_my_appointment_by_type'))
        <script type="text/javascript">
            jQuery(document).ready(function () {
                Home.initMyAppointmentsByType('today', '', '', '');
            });
        </script>
    @endif
@endsection