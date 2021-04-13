@extends('template')


@section('title')
Manage Server
@endsection



@section('content')
<ol class="breadcrumb mb-4">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="serveriptop"></span></b></li>
    <li class="ml-1 breadcrumb-item active">Sites:<b><span class="ml-1" id="serversites"></span></b></li>
    <li class="ml-1 breadcrumb-item active">Ping:<b><span class="ml-1" id="serverping"><i class="fas fa-circle-notch fa-spin"></i></span></b></li>
</ol>
<div class="row">
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-microchip fs-fw mr-1"></i>
                CPU Realtime Load
            </div>
            <div class="card-body">
                <canvas id="cpuChart" width="100%" height="40"></canvas>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-memory fs-fw mr-1"></i>
                RAM Realtime Usage
            </div>
            <div class="card-body">
                <canvas id="ramChart" width="100%" height="40"></canvas>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle fs-fw mr-1"></i>
                Server information
            </div>
            <div class="card-body">
                <p>Server name:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Production" id="servername" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>Server IP:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="123.123.123.123" id="serverip" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>Server Provider:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Digital Ocean" id="serverprovider" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>Server Location:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Amsterdam" id="serverlocation" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="updateServer">Update</button>
                </div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-power-off fs-fw mr-1"></i>
                System services
            </div>
            <div class="card-body">
                <p>nginx</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartnginx">Restart <i class="fas fa-circle-notch fa-spin d-none" id="loadingnginx"></i></button>
                </div>
                <div class="space"></div>
                <p>PHP-FPM</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartphp">Restart <i class="fas fa-circle-notch fa-spin d-none" id="loadingphp"></i></button>
                </div>
                <div class="space"></div>
                <p>MySql</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartmysql">Restart <i class="fas fa-circle-notch fa-spin d-none" id="loadingmysql"></i></button>
                </div>
                <div class="space"></div>
                <p>Redis</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartredis">Restart <i class="fas fa-circle-notch fa-spin d-none" id="loadingredis"></i></button>
                </div>
                <div class="space"></div>
                <p>Supervisor</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" id="restartsupervisor">Restart <i class="fas fa-circle-notch fa-spin d-none" id="loadingsupervisor"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tools fs-fw mr-1"></i>
                Tools
            </div>
            <div class="card-body">
                <p>PHP CLI version:</p>
                <div class="input-group">
                    <select class="form-control" id="phpver">
                        <option value="8.0" id="php80">8.0</option>
                        <option value="7.4" id="php74">7.4</option>
                        <option value="7.3" id="php73">7.3</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="changephp"><i class="fas fa-edit"></i></button>
                    </div>
                </div>
                <div class="space"></div>
                <p>Manage Cron Jobs:</p>
                <div>
                    <button class="btn btn-primary" type="button" id="editcrontab">Edit Crontab</button>
                </div>
                <div class="space"></div>
                <p>Reset cipi user password:</p>
                <div>
                    <button class="btn btn-danger" type="button" id="rootreset">Require Reset</button>
                </div>
                <div class="space"></div>
                <p>HD memory usage:</p>
                <div>
                    <span class="btn" id="hd"><i class="fas fa-circle-notch fa-spin"></i></span>
                </div>
                <div class="space"></div>
                <p>Cipi build version:</p>
                <div>
                    <span class="btn btn-secondary" id="serverbuild"><i class="fas fa-circle-notch fa-spin"></i></span>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<input type="hidden" id="currentip">
<div class="modal fade" id="updateServerModal" tabindex="-1" role="dialog" aria-labelledby="updateServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="updateserverdialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateServerModalLabel">Update server information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are your sure to update server information?</p>
                <p class="d-none" id="ipnotice"><b>YOU ARE UPDATING SERVER IP!<br>BE AWARE THAT IF NEW IP: <span id="newip" class="text-danger"></span> IS NOT CORRECT YOU COULD LOST YOUR SERVER CONNECTION!</b></p>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="submit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="crontabModal" tabindex="-1" role="dialog" aria-labelledby="crontabModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crontabModalLabel">Server Crontab</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Edit server crontab:</p>
                <div id="crontab" style="height:250px;width:100%;"></div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="crontabsubmit">Save <i class="fas fa-circle-notch fa-spin d-none" id="crontableloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="rootresetModal" tabindex="-1" role="dialog" aria-labelledby="rootresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rootresetModalLabel">Request password reset</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to reset cipi user password?</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="rootresetsubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="rootresetloading"></i></button>
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
<script src="/assets/js/server.js?v=20210413"></script>
@endsection