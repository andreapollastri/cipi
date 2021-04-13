@extends('template')


@section('title')
Settings
@endsection



@section('content')
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user fs-fw mr-1"></i>
                Change Username
            </div>
            <div class="card-body">
                <p>Current username: <b><span id="currentuser"></span></b></p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="New username (at least 6 chars)" id="newuser" autocomplete="off" />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="changeuser"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-key fs-fw mr-1"></i>
                Change Password
            </div>
            <div class="card-body">
                <p>Update your password</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="New password (at least 8 chars)" id="newpass" autocomplete="off" />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="changepass"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-globe fs-fw mr-1"></i>
                Panel URL
            </div>
            <div class="card-body">
                <p>Custom panel domain/subdomain</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="panel.domain.ltd" id="panelurl" autocomplete="off" />
                    <div class="input-group-append">
                        <button class="btn btn-warning" type="button" id="panelurlssl" data-toggle="tooltip" data-placement="top" title="Require SSL"><i class="fas fa-lock"></i></button>
                    </div>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="panelurlsubmit"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-code fs-fw mr-1"></i>
                Panel API
            </div>
            <div class="card-body">
                <p>API Endpoint:  <b>{{ URL::to('/api/') }}</b></p>
                <div class="text-center">
                    <button class="btn btn-primary mr-3" type="button" id="newapikey">Renew API Key</button>
                    <a href="/api/docs" target="_blank">
                        <button class="btn btn-warning" type="button">Documentation</button>
                    </a>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<div class="modal fade" id="authorizeModal" tabindex="-1" role="dialog" aria-labelledby="authorizeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorizeModalLabel">Action Authorization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>To authorize this action insert your current password:</p>
                <div class="input-group">
                    <input class="form-control" type="password" id="currentpass" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="submit">Submit <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('css')

@endsection



@section('js')
<script src="/assets/js/settings.js?v=20210413"></script>
@endsection