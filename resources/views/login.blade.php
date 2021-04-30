<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>{{ config('cipi.name') }} | Login</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
        <link rel="icon" type="image/png" href="/favicon.png" />
        <link href="/assets/css/app.css" rel="stylesheet" />
        <style>
            /* .auth-bg {
                background-image: url("/assets/bg/{{ config('cipi.background') }}.jpg");
                background-size: cover;
                background-position: bottom;
            } */
        </style>
    </head>

    <body class="bg-secondary">

        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content" class="auth-bg">
                <main>
                    <div class="container">
                        <div class="row justify-content-center align-items-center vh-100">
                            <div class="col-lg-5">
                                <div class="card shadow rounded-lg border-0 mt-5">
                                    <div class="card-body">
                                        <form>
                                            <div class="text-center">
                                                <h1>Login</h1>
                                                <p class="text-secondary">Enter your credentials to access your account</p>
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="username">Username</label>
                                                <input class="form-control py-4" id="username" type="email" placeholder="administrador" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="password">Password</label>
                                                <input class="form-control py-4" id="password" type="password" placeholder="********" />
                                            </div>
                                            <div class="form-group d-flex mt-4 mb-0">
                                                <button class="btn btn-primary btn-block" id="login" type="button">Login <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="text-center mb-4">
                                        <div class="small font-weight-bold">&copy{{ Date('Y') }} - <a href="{{ config('cipi.website') }}" target="_blank"> {{ config('cipi.name') }}</a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/app.js"></script>
        <script>

            //Clear current auth
            localStorage.clear();
        
            //Validation check
            function loginValidate() {
                validation = true;
                if(!$('#username').val()) {
                    $('#username').addClass('is-invalid');
                    validation = false;
                }
                if(!$('#password').val()) {
                    $('#password').addClass('is-invalid');
                    validation = false;
                }
                return validation;
            }

            //Login
            function loginSubmit() {
                if(loginValidate() == true) {
                    $.ajax({
                        url: '/auth',
                        type: 'POST',
                        headers: {
                            'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            'username': $('#username').val(),
                            'password': $('#password').val()
                        },
                        beforeSend: function() {
                            $('#loading').removeClass('d-none');
                            $('#username').removeClass('is-invalid');
                            $('#password').removeClass('is-invalid');
                        },
                        complete: function() {
                            $('#username').blur();
                            $('#password').blur();
                            $('#loading').addClass('d-none');
                        },
                        success: function(data) {
                            localStorage.access_token=data.access_token;
                            localStorage.refresh_token=data.refresh_token;
                            localStorage.username=data.username;
                            window.location.replace('/dashboard');
                        },
                        error: function() {
                            $('#username').addClass('is-invalid');
                            $('#password').addClass('is-invalid');
                        }
                    });
                }
            }

            //Keyup Validation
            $('#username').keyup(function() {
                $('#username').removeClass('is-invalid');
            });
            $('#password').keyup(function() {
                $('#password').removeClass('is-invalid');
            });
            $('#username').blur(function() {
                loginValidate();
            });
            $('#password').blur(function() {
                loginValidate();
            });

            //Submit via Mouse Click
            $('#login').on('click', function() {
                loginSubmit();
            });

            //Submit via Enter Key
            $(document).keypress(function(e) {
                var keycode = (e.keyCode ? e.keyCode : e.which);
                if(keycode == '13'){
                    loginSubmit(); 
                }
            });

        </script>

    </body>
    
</html>