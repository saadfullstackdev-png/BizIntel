@extends('layouts.app-rs')

<style type="text/css">
    .cf_label {
        width: 100%;
    }

    .cf_input_question {
        font-size: 18px !important;
        color: #000 !important;
    }

    .cf_input_option {
        padding-top: 10px !important;
    }

    .cf_card {
        padding-bottom: 60px;
    }

    .cf_input_option .md-checkbox label > .box {
        width: 15px;
        height: 15px;
    }

    .rs .md-checkbox label > .box {
        top: 10px;
    }

    .rs .form-group.form-md-line-input .form-control {
        border-bottom: 0;
        font-size: 16px;
        display: inline-block;
    }

    .cf_input_option label {
        width: 90%;
    }

    .cf_main_title {
        padding-bottom: 30px;
    }

    .cf_form_internal_title {
        background: purple;
        font-weight: bold;
        font-size: 18px;
        color: #fff;
    }

    .cf-question-headings {
        color: #000;
        font-weight: 500;
    }

    .cf_form_internal_title {
        background: #35a1d4;
        font-weight: bold;
        font-size: 18px;
        color: #fff;
        display: inline-block;
        padding: 10px;
        position: relative;
    }

    h3.cf_form_internal_title:after {
        content: "";
        width: 0;
        height: 0;
        border-left: 3px solid transparent;
        border-right: 20px solid transparent;
        border-top: 39px solid #35a1d4;
        position: absolute;
        right: -20px;
        top: 0px;
    }

    .cf-input-border {
        border-bottom: 1px solid #ddd !important;
    }
</style>
@section('title')
    <!-- BEGIN PAGE TITLE-->
    <h1 class="page-title">@lang('global.custom_form_feedbacks.title')</h1>
    <!-- END PAGE TITLE-->
@endsection

@section('content')
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption font-green-sharp">
                <i class="icon-pencil font-green-sharp"></i>
                <span class="caption-subject bold uppercase"> @lang('global.app_edit')</span>
            </div>
            <div class="actions">
                <a href="{{ route('admin.custom_form_feedbacks.index') }}"
                   class="btn dark pull-right">@lang('global.app_back')</a>
            </div>
        </div>
        <div class="portlet-body rs">
            <form role="form" action="#">

                <div class="form-group form-md-line-input cf_main_title">
                    <div class="form-group form-md-line-input cf_input_question">
                        <h1 class="rs-head">Form Name</h1>
                    </div>
                </div>

                <div class="form-group form-md-line-input cf_main_title">
                    <div class="form-group form-md-line-input cf_input_question">
                        <P>This is paragraph</P>
                    </div>
                </div>

                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf-question-headings">Multi Selector</h3>
                    <div class="cf_input_option">
                        <div class="md-checkbox">
                            <input type="checkbox" id="checkbox2_1" name="" value="1"
                                   class="md-check">
                            <label class="cf_label" for="checkbox2_1">
                                <span></span>
                                <span class="check" style="margin-top: 8px;"></span>
                                <span class="box"></span> <h4>Option</h4>
                            </label>
                        </div>
                        <div class="md-checkbox">
                            <input type="checkbox" id="checkbox2_1" name="" value="2"
                                   class="md-check">
                            <label class="cf_label" for="checkbox2_1">
                                <span></span>
                                <span class="check" style="margin-top: 8px;"></span>
                                <span class="box"></span> <h4>Option</h4>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf-question-headings">Single Selector</h3>
                    <div class="cf_input_option">
                        <div class="md-radio">
                            <input type="radio" id="checkbox2_9" name="radio2" value="1233"
                                   class="md-radiobtn">
                            <label class="cf_label" for="checkbox2_9">
                                <span></span>
                                <span class="check" style="margin-top: 8px;"></span>
                                <span class="box"></span> <h4>Option</h4></label>
                        </div>
                        <div class="md-radio">
                            <input type="radio" id="checkbox2_9" name="radio2" value="112"
                                   class="md-radiobtn">
                            <label class="cf_label" for="checkbox2_9">
                                <span></span>
                                <span class="check" style="margin-top: 8px;"></span>
                                <span class="box"></span> <h4>Option</h4></label>
                        </div>
                    </div>
                </div>


                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf_form_internal_title">Title</h3>
                </div>


                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf-question-headings">Short Question</h3>
                    <div class="form-group cf_input_option">
                        <label>
                            <input type="text" class="form-control cf-input-border"
                                   name="option" placeholder="Short Answer">
                        </label>
                    </div>
                </div>


                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf-question-headings">Paragraph</h3>
                    <div class="form-group cf_input_option">
                        <label>
                                                        <textarea class="form-control cf-input-border" value=""
                                                                  name="Paragraph Answer"
                                                                  placeholder="Paragraph Answer"></textarea>
                        </label>
                    </div>
                </div>


                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf_form_internal_title">Text Field</h3>
                </div>


                <div class="form-group form-md-line-input cf_card">
                    <h3 class="cf-question-headings">Selection Box</h3>
                    <div class="form-group form-md-line-input cf_input_option">
                        <label>
                            <select class="form-control cf-input-border" name="delivery">
                                <option value="">Select</option>
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                                <option value="3">Option 3</option>
                            </select>
                        </label>
                    </div>
                </div>


                <div class="margin-top-10">
                    <a href="javascript:;" class="btn green">Save Changes </a>
                    <a href="javascript:;" class="btn default">Cancel </a>
                </div>
            </form>
        </div>
    </div>




@stop

@section('javascript')
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/jquery.validate.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('metronic/assets/global/plugins/jquery-validation/js/additional-methods.min.js') }}"
            type="text/javascript"></script>
    <script src="{{ url('js/admin/custom_form_feedbacks/fields.js') }}" type="text/javascript"></script>


@endsection

