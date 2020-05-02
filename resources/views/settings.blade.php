@extends('layouts.app')



@section('title')
Settings
@endsection



@section('content')
@if(Session::has('alert-success'))
<div class="alert alert-success" role="alert">
    <b><i class="fa fa-check" aria-hidden="true"></i></b> {{ Session::get('alert-success') }}
</div>
@endif
@if(Session::has('alert-error'))
<div class="alert alert-danger" role="alert">
    <b><i class="fa fa-times" aria-hidden="true"></i></b> {{ Session::get('alert-error') }}
</div>
@endif
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                Edit your information
            </div>
            <div class="card-body">
                <form method="POST" action="/settings/profile" class="ws-validate">
                    @csrf
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="name" type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required autocomplete="name" autofocus>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="email" class="col-md-4 col-form-label text-md-right">E-mail</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="email" type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" required autocomplete="email">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="space"></div>
        <div class="card sm-12">
            <div class="card-header text-center">
                Change your password
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <form method="POST" action="/settings/password" class="ws-validate">
                        @csrf
                        <div class="form-group row">
                            <label for="password" class="col-md-6 col-form-label text-md-right">Password</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="current" type="password" class="form-control" name="current" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-md-6 col-form-label text-md-right">New password *</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="password" type="password" class="form-control" name="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-6 col-form-label text-md-right">Confirm password</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-6">
                                <button type="submit" class="btn btn-primary">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row text-center">
                    <div class="col-xs-12 text-center w-75 pwd-info">
                        <p>* at least 8 chars with uppercase, lowercase and numbers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                SMTP CONFIGURATION
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <form method="POST" action="/settings/smtp" class="ws-validate">
                        @csrf
                        <div class="form-group row">
                            <label for="host" class="col-md-6 col-form-label text-md-right">Host</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="host" type="text" class="form-control" name="host" value="{{ $smtp->host }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="port" class="col-md-6 col-form-label text-md-right">Port</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="port" type="text" class="form-control" name="port" value="{{ $smtp->port }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="encryption" class="col-md-6 col-form-label text-md-right">Encryption</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="encryption" type="text" class="form-control" name="encryption" value="{{ $smtp->encryption }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="username" class="col-md-6 col-form-label text-md-right">Username</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="username" type="text" class="form-control" name="username" value="{{ $smtp->username }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-md-6 col-form-label text-md-right">Password</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="password" type="password" class="form-control" name="password" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="from" class="col-md-6 col-form-label text-md-right">From</label>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input id="from" type="text" class="form-control" name="from" value="{{ $smtp->from }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-6">
                                <button type="submit" class="btn btn-primary">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')

@endsection



@section('css')
<style>
    .pwd-info {
        font-size: 12px;
    }
</style>
@endsection



@section('js')
<script>
    $('#password').keyup(function() {
        if ($('#password').val() != $('#password-confirm').val()) {
            $('#password-confirm').setCustomValidity("Passwords don't match");
        } else {
            $('#password-confirm').setCustomValidity('');
        }
    });
    $('#password-confirm').keyup(function() {
        if ($('#password').val() != $('#password-confirm').val()) {
            $('#password-confirm').setCustomValidity("Passwords don't match");
        } else {
            $('#password-confirm').setCustomValidity('');
        }
    });
</script>
@endsection
