@extends('layouts.app')



@section('title')
Servers
@endsection



@section('content')
<div class="row">
    <div class="col">
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> NEW SERVER
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
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">Server</th>
                        <th class="text-center">IP</th>
                        <th class="text-center d-none d-lg-table-cell">Provider</th>
                        <th class="text-center d-none d-lg-table-cell">Location</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($servers as $server)
                        <tr>
                            <td class="text-center">
                                @if ($server->status == 0)
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#setupModal" data-servercode="{{ $server->servercode }}" data-serverip="{{ $server->ip }}">"{{ $server->name }}" has to be installed</button>
                                @elseif ($server->status == 1)
                                    <button type="button" class="btn btn-warning btn-sm">"{{ $server->name }}" is coming...</button>
                                @else
                                    <a href="/server/{{ $server->servercode }}"><b>{{ $server->name }}</b></a>
                                    <i data-toggle="modal" data-target="#changenameModal" class="far fa-edit" data-servercode="{{ $server->servercode }}" data-servername="{{ $server->name }}" style="color:gray; cursor: pointer;"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $server->ip }}
                                <i data-toggle="modal" data-target="#changeipModal" class="far fa-edit" data-servercode="{{ $server->servercode }}" data-serverip="{{ $server->ip }}" style="color:gray; cursor: pointer;"></i>
                            </td>
                            <td class="text-center d-none d-lg-table-cell">
                                @switch($server->provider)
                                    @case('aws')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('Aws')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('AWS')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('Amazon')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('amazon')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('AMAZON')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('AmazonWebService')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('Amazon web service')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('amazonwebservice')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('AMAZONWEBSERVICE')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('Amazon Web Service')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('amazon web service')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('AMAZON WEB SERVICE')
                                        <i class="fab fa-aws"></i>
                                        @break
                                    @case('DO')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('do')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('Digital Ocean')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('digitalocean')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('DIGITAL OCEAN')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('digital ocean')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('DigitalOcean')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('DIGITALOCEAN')
                                        <i class="fab fa-digital-ocean"></i>
                                        @break
                                    @case('Google')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('google')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('GOOGLE')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('GoogleCloud')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('Google Cloud')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('google cloud')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('GOOGLE CLOUD')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('GOOGLECLOUD')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('googlecloud')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('glg')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('GLG')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @case('Glg')
                                        <i class="fab fa-google"></i>
                                        @break
                                    @default
                                    {{ $server->provider }}
                                @endswitch
                            </td>
                            <td class="text-center d-none d-lg-table-cell">{{ $server->location }}</td>
                            <td class="text-center">
                            @if($server->status == 2)
                            <a href="#" data-toggle="modal" data-target="#resetModal" data-servername="{{ $server->name }}" data-servercode="{{ $server->servercode }}" style="margin-right: 18px;">
                                <i class="fas fa-key" style="color:gray;"></i>
                            </a>
                            @endif
                            <i data-toggle="modal" data-target="#deleteModal" class="fas fa-trash-alt" data-servercode="{{ $server->servercode }}" data-servername="{{ $server->name }}" style="color:gray; cursor: pointer;"></i>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection



@section('extra')
<!-- CREATE -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/server/create" method="POST" class="ws-validate">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create a new server</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">Name*</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="name" type="text" class="form-control" name="name" required autocomplete="off" autofocus placeholder="E.g. 'Production'">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="ip" class="col-md-4 col-form-label text-md-right">Ip*</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="ip" type="text" class="form-control" name="ip" autocomplete="off" required placeholder="E.g. '123.123.123.123'">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="provider" class="col-md-4 col-form-label text-md-right">Provider</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="provider" type="text" class="form-control" name="provider" autocomplete="off" placeholder="E.g. 'Digital Ocean'">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="location" class="col-md-4 col-form-label text-md-right">Location</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="location" type="text" class="form-control" name="location" autocomplete="off" placeholder="E.g. 'New York - NYC3'">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create server</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SETUP -->
<div class="modal fade" id="setupModal" tabindex="-1" role="dialog" aria-labelledby="setupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setupModalLabel">Server Setup</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                To install your server:<br>
                <ul>
                    <li>Use a clean VPS with Ubuntu Server 18.04 LTS or 20.04 LTS</li>
                    <li>Login into your VPS via SSH (as root):<br>
                        <code><i>ssh root@<span class="server-ip"></span></i></code>
                    </li>
                    <li>Run this command:<br>
                        <code><i>wget -O - {{ url('/sh/go') }}/<span class="server-id"></span> | bash</i></code>
                    </li>
                    <li>Installation may take up to ten minutes depending on your server internet connection speed</li>
                    <li>Before you install Cipi, please make sure your server is a clean Ubuntu 18.04 or 20.04 x86_64 LTS VPS (Fresh installation)</li>
                    <li>Hardware Requirements: minimum 1GB free HDD / at least 1 core processor / 512MB or more RAM / 1 public IPv4 address</li>
                    <li>Please open port 22, 80 and 443 of your firewall to install Cipi</li>
                    <li>Cipi doesn't work with NAT VPN and OpenVZ or in localhost</li>
                    <li>AWS disables root login by default. To gain root privileges, login as default user and then use command 'sudo -s'</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- CHANGE IP -->
<div class="modal fade" id="changeipModal" tabindex="-1" role="dialog" aria-labelledby="changeipModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/server/changeip" method="POST" class="ws-validate">
                @csrf
                <input type="hidden" name="servercode" id="server-code-ip" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeipModalLabel">Update Server IP</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" id="server-ip" required class="form-control" name="ip" autocomplete="off">
                            </div>
                            <i class="fas fa-exclamation-circle" style="margin-left: 5px;"></i> Before submitting changes, double check the IP!
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- CHANGE NAME -->
<div class="modal fade" id="changenameModal" tabindex="-1" role="dialog" aria-labelledby="changenameModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/server/changename" method="POST" class="ws-validate">
                @csrf
                <input type="hidden" name="servercode" id="server-code-name" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="changenameModalLabel">Update Server Name</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" id="server-name" required class="form-control" name="name" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/server/destroy" method="POST" class="ws-validate">
                @csrf
                <input type="hidden" name="servercode" id="server-code" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete server</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h4>Are you sure to delete server <b><span id="server-name"></span></b>?</h4>
                            <div class="space"></div>
                            <div class="form-group">
                                <select class="form-control" name="server_id" required id="server-list">
                                    <option value="">Select...</option>
                                    <option value="">YES! Delete this server.</option>
                                </select>
                            </div>
                            <h6 class="text-danger text-center"><b>This can't be undone and you'll lose access to your server</b></h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Delete server</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- RESET -->
<div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Reset root user</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                Are you sure to reset cipi's user (<span class="ajax-root"></span> root user)?<br><br>
                <div id="root-area"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection



@section('css')
<link rel="stylesheet" href="https://allyoucan.cloud/cdn/datatable/1.10.13/css/dataTables.css">
@endsection



@section('js')
<script src="https://allyoucan.cloud/cdn/datatable/1.10.13/js/dataTables.js"></script>
<script>
	$(document).ready(function() {
		$('#dataTable').DataTable();
	});
</script>
<script>
$('#setupModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var servercode = button.data('servercode')
    var serverip = button.data('serverip')
    var modal = $(this)
    modal.find('.server-id').text(servercode)
    modal.find('.server-ip').text(serverip)
})
</script>
<script>
$('#deleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var servercode = button.data('servercode')
    var servername = button.data('servername')
    var modal = $(this)
    modal.find('#server-code').val(servercode)
    modal.find('#server-name').text(servername)
})
$('#changeipModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var servercode = button.data('servercode')
    var serverip = button.data('serverip')
    var modal = $(this)
    modal.find('#server-code-ip').val(servercode)
    modal.find('#server-ip').val(serverip)
})
$('#changenameModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var servercode = button.data('servercode')
    var servername = button.data('servername')
    var modal = $(this)
    modal.find('#server-code-name').val(servercode)
    modal.find('#server-name').val(servername)
})
</script>
<script>
    $('#resetModal').on('show.bs.modal', function (event) {
        $("#root-area").empty();
        $("#root-area").html('<input type="hidden" name="server" value="" class="ajax-server"><button class="btn btn-primary" id="reset-root">Yes, continue!</button>');
        var button = $(event.relatedTarget)
        var server = button.data('servername')
        var servercode = button.data('servercode')
        var modal = $(this)
        modal.find('.ajax-root').text(server)
        modal.find('.ajax-server').val(servercode)
        function resetroot(servercode) {
            $("#root-area").empty();
            $("#root-area").html('<center><i class="fas fa-spinner fa-spin"></center>');
            $.ajax({
                url: "/server/reset/"+servercode,
                type: "GET",
                success: function(response){
                    $("#root-area").empty();
                    $("#root-area").html('<center>Password for "cipi" user has been changed:<br><b>'+response+'</b></center>');
                },
                error: function(response) {
                    $("#root-area").empty();
                    $("#root-area").html('<center><i>Error with server connection. Retry!</i></center>');
                }
            });
        }
        $("#reset-root").click(function() {
            resetroot($('.ajax-server').val());
        });
    })
</script>
@endsection
