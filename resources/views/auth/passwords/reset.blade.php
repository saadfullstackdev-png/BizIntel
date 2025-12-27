{{--
@extends('layouts.auth_new')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger"><strong>Whoops!</strong> There were problems with input:
            <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="forget-form" role="form" method="POST" action="{{ url('password/reset') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="email" autocomplete="off"
                       placeholder="Email" name="email" value="{{ old('email') }}">
            </div>
        </div>

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="password" placeholder="Password"
                       name="password">
            </div>
        </div>

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="password"
                       placeholder="Confirm Password" name="password_confirmation">
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary" style="margin-right: 15px;">Reset
                    password
                </button>
            </div>
        </div>
    </form>
@endsection
--}}
@extends('layouts.auth_newforget')

@section('content')
    @if (count($errors) > 0)
        <div class="alert alert-danger"><strong>Whoops!</strong> There were problems with input:
            <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="forget-form" role="form" method="POST" action="{{ url('password/reset') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="token" value="{{ $token }}">
        <h3>Reset Password</h3>
        <p> Enter your e-mail and new password address below to reset your password. </p>
        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="email" autocomplete="off"
                       placeholder="Email" name="email" value="{{ old('email') }}">
            </div>
        </div>

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="password" placeholder="Password"
                       name="password">
            </div>
        </div>

        <div class="form-group">
            <div class="input-icon">
                <i class="fa fa-envelope"></i>
                <input class="form-control placeholder-no-fix" type="password"
                       placeholder="Confirm Password" name="password_confirmation">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" style="margin-right: 15px;">Reset password</button>
        </div>
    </form>
@endsection
