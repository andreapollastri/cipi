@extends('template')


@section('title')
Servers
@endsection



@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header text-right">
                <button class="btn btn-sm btn-secondary" id="newServer">
                    <i class="fas fa-plus mr-1"></i><b>New Server</b>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dt" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center d-none d-md-table-cell">Name</th>
                                <th class="text-center">IP</th>
                                <th class="text-center d-none d-lg-table-cell">Provider</th>
                                <th class="text-center d-none d-xl-table-cell">Location</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<div class="modal fade" id="newServerModal" tabindex="-1" role="dialog" aria-labelledby="newServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="newserverdialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newServerModalLabel">Add a new server</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="newserverform">
                    <label for="newservername">Server name</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newservername" placeholder="Production Server" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverip">Server IP</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverip" placeholder="123.45.67.89" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverprovider">Server provider</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverprovider" placeholder="Digital Ocean" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverlocation">Server location</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverlocation" placeholder="Amsterdam" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="submit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                    </div>
                </div>
                <div id="newserverok" class="d-none">
                    <p><b>To install your server:</b>
                        <ul>
                            <li>Use a clean Ubuntu Server 20.04 LTS fresh installation VPS</li>
                            <li>Login into your VPS via SSH (as root):<br>
                                <code><i>ssh root@<span id="newserverssh"></span></i></code>
                            </li>
                            <li>Run this command:<br>
                                <code><i>wget -O - {{ URL::to('/sh/setup/') }}/<span id="newserverid"></span> | bash</i></code>
                            </li>
                            <li>Installation may take up to thirty minutes depending on your server resources</li>
                            <li>Be sure that ports 22, 80 and 443 of your VPS firewall are open</li>
                            <li>AWS disables root login by default. Use command 'sudo -s' to run as root</li>
                            <li>Cipi doesn't work with NAT VPN and OpenVZ or in localhost</li>
                            <li>Before install Cipi, please make sure your server is a clean Ubuntu 20.04 LTS VPS</li>
                        </ul>
                    </p>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="installServerModal" tabindex="-1" role="dialog" aria-labelledby="installServerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installServerModalLabel">Server Setup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><b>To install your server:</b>
                    <ul>
                        <li>Use a clean Ubuntu Server 20.04 LTS fresh installation VPS</li>
                        <li>Login into your VPS via SSH (as root):<br>
                            <code><i>ssh root@<span id="installserverssh"></span></i></code>
                        </li>
                        <li>Run this command:<br>
                            <code><i>wget -O - {{ URL::to('/sh/setup/') }}/<span id="installserverid"></span> | bash</i></code>
                        </li>
                        <li>Installation may take up to thirty minutes depending on your server resources</li>
                        <li>Be sure that ports 22, 80 and 443 of your VPS firewall are open</li>
                        <li>AWS disables root login by default. Use command 'sudo -s' to run as root</li>
                        <li>Cipi doesn't work with NAT VPN and OpenVZ or in localhost</li>
                        <li>Before install Cipi, please make sure your server is a clean Ubuntu 20.04 LTS VPS</li>
                    </ul>
                </p>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteServerModal" tabindex="-1" role="dialog" aria-labelledby="deleteServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServerModalLabel">Delete server</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete server <b><span id="deleteservername"></span></b>?</p>
                <div class="space"></div>
                <label for="deleteserverip">To confirm it write server IP: <i><span id="deleteserveriptocopy"></span></i></label>
                <div class="input-group">
                    <input class="form-control" type="text" id="deleteserverip" autocomplete="off" />
                </div>
                <input type="hidden" id="deleteserverid" value="" />
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="delete">Delete <i class="fas fa-circle-notch fa-spin d-none" id="loadingdelete"></i></button>
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
<script src="/assets/js/servers.js?v=20210413"></script>
@endsection