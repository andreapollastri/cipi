@extends('layouts.guest')

@section('title')
Login
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                    <div class="col-lg-6">
                        <div class="p-5">
                            <img src="/logo.png" class="mx-auto d-block">
                            <div class="space"></div>
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">LOGIN</h1>
                            </div>
                            <form method="POST" action="{{ route('login') }}" class="user ws-validate">
                                @csrf
                                <div class="form-group">
                                    <input type="email" name="email" class="form-control form-control-user" aria-describedby="emailHelp" placeholder="john.doe@domain.ltd" required autocomplete="off" autofocus>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control form-control-user" placeholder="password" required autocomplete="current-password">
                                </div>
                                <input type="submit" class="btn btn-primary btn-user btn-block" value="Login">
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="{{ route('password.request') }}">Forgot Password?</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
