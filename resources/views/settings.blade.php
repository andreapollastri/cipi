@extends('layouts.app')



@section('title')
Settings
@endsection



@section('content')
<div class="row">
    <div class="col">
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" data-toggle="modal" data-target="#apikeyModal">
            <i class="fas fa-key fa-sm text-white-50"></i> API KEY
        </a>
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" style="margin-right:8px" data-toggle="modal" data-target="#exportModal">
            <i class="fas fa-arrow-down text-white-50"></i> EXPORT
        </a>
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" style="margin-right:8px" data-toggle="modal" data-target="#importModal">
            <i class="fas fa-arrow-up fa-sm text-white-50"></i> IMPORT
        </a>
    </div>
</div>
<div class="space"></div>
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
                Edit username
            </div>
            <div class="card-body">
                <form method="POST" action="/settings/username" class="ws-validate">
                    @csrf
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
                SMTP Configuration
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-xs-12 text-center w-75">
                        <p>Useful for password reset</p>
                    </div>
                </div>
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
<!-- API KEY -->
<div class="modal fade" id="apikeyModal" tabindex="-1" role="dialog" aria-labelledby="apikeyModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apikeyModalLabel">Api Key</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="space"></div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <b>APP KEY</b><br>
                        <i>{{ $user->appkey }}</i><br><br>
                        <b>APP SECRET</b><br>
                        <span id="app-secret"><i>{{ $user->appsecret }}</i></span>
                    </div>
                </div>
                <div class="space"></div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a href="#" class="btn btn-sm btn-primary shadow-sm" id="apikey-renew">
                            <i class="fas fa-sync fa-sm text-white-50"></i> Renew App Secret (confirm required)
                        </a>
                        <a href="#" class="btn btn-danger shadow-sm" id="apikey-confirm">
                            Are you really sure to renew App Secret?
                        </a>
                    </div>
                </div>
                <div class="space"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- IMPORT -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/settings/import" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Cipi migration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="space"></div>
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            Copy your migration key into this area:<br>
                            <textarea name="cipikey" class="form-group" style="width:100%;height:80px;"></textarea><br>
                            <b><i>All data in current Cipi db will be delete!</b></i>
                            <div class="space"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <a href="#" class="btn btn-sm btn-primary shadow-sm" id="import-confirm">
                                <i class="fas fa-arrow-up fa-sm text-white-50"></i> Import migration
                            </a>
                            <input type="submit" value="Click to confirm migration import" class="btn btn-danger shadow-sm" id="import-submit">
                        </div>
                    </div>
                    <div class="space"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- EXPORT -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Cipi migration</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="space"></div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        This is your Cipi migration key:<br>
                        <textarea name="cipikey" class="form-group" style="width:100%;height:80px;" id="export-key" readonly>Hold on... Cipi is generating a new migration key...</textarea><br>
                        <div class="space"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <a href="#" class="btn btn-sm btn-primary shadow-sm" id="export-copy">
                            <i class="fas fa-arrow-down fa-sm text-white-50"></i> Copy migration key to clipboard
                        </a>
                    </div>
                </div>
                <div class="space"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
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
<script>
$('#apikeyModal').on('show.bs.modal', function (event) {
    $('#apikey-renew').show();
    $('#apikey-confirm').hide();
})
</script>
<script>
$('#apikey-renew').click(function() {
    $('#apikey-renew').hide();
    $('#apikey-confirm').show();
});
$('#apikey-confirm').click(function() {
    $('#apikey-confirm').hide();
    $('#app-secret').empty();
    $("#app-secret").html('<i class="fas fa-spinner fa-spin">');
    $.ajax({
        url: "/settings/secret",
        type: "GET",
        success: function(response){
            $("#app-secret").empty();
            $("#app-secret").html('<i>'+response+'</i>');
            $('#apikey-renew').show();
        },
        error: function(response) {
            $("#app-secret").empty();
            $("#app-secret").html('<b style="color:red">Internal error. Retry!</b>');
            $('#apikey-renew').show();
        }
    });
});
</script>
<script>
    $('#importModal').on('show.bs.modal', function (event) {
        $('#import-confirm').show();
        $('#import-submit').hide();
    });
    $('#import-confirm').click(function() {
        $('#import-confirm').hide();
        $('#import-submit').show();
    });
</script>
<script>
    $('#exportModal').on('show.bs.modal', function (event) {
        $.ajax({
        url: "/settings/export",
        type: "GET",
        success: function(response){
            $("#export-key").empty();
            $("#export-key").val(response);
        },
        error: function(response) {
            $("#export-key").empty();
            $("#export-key").val('Error. Retry!');
        }
    });
    })
</script>
<script>
    $("#export-copy").click(function(){
        $("#export-key").select();
        document.execCommand('copy');
    });
</script>
@endsection
