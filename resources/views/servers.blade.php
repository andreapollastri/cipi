@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Servers') }}</h1>
    <a href="#" class="btn btn-sm btn-secondary shadow-sm " data-toggle="modal" data-target="#createModal" ><i class="fas fa-plus"></i><span class="d-none d-md-inline"> {{ __('CREATE NEW') }}</span></a>   
</div>
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
                                @if ($server->complete == 0)
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#setupModal" data-servercode="{{ $server->servercode }}" data-serverip="{{ $server->ip }}">"{{ $server->name }}" {{ __('has to be install') }}</button>
                                @elseif ($server->complete == 1)
                                    <button type="button" class="btn btn-warning btn-sm">"{{ $server->name }}" {{ __('is coming...') }}</button>
                                @else
                                    {{ $server->name }}
                                @endif
                            </td>
                            <td class="text-center">{{ $server->ip }}</td>
                            <td class="text-center d-none d-lg-table-cell">{{ $server->provider }}</td>
                            <td class="text-center d-none d-lg-table-cell">{{ $server->location }}</td>
                            <td class="text-center">
                            <i class="fas fa-trash-alt" data-toggle="modal" data-target="#deleteModal" class="fas fa-trash-alt" data-servercode="{{ $server->servercode }}" data-servername="{{ $server->name }}" style="color:gray; cursor: pointer;"></i>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


<!-- CREATE -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('servercreate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">{{ __('Create a new server') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}*</label>
                        <div class="col-md-6">
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" required autocomplete="off" autofocus placeholder="E.g. 'Production'">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="ip" class="col-md-4 col-form-label text-md-right">{{ __('Ip') }}*</label>
                        <div class="col-md-6">
                            <input id="ip" type="text" class="form-control" name="ip" autocomplete="off" required placeholder="E.g. '123.123.123.123'">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="provider" class="col-md-4 col-form-label text-md-right">{{ __('Provider') }}</label>
                        <div class="col-md-6">
                            <input id="provider" type="text" class="form-control" name="provider" autocomplete="off" placeholder="E.g. 'Digital Ocean'">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="location" class="col-md-4 col-form-label text-md-right">{{ __('Location') }}</label>
                        <div class="col-md-6">
                            <input id="location" type="text" class="form-control" name="location" autocomplete="off" placeholder="E.g. 'New York - NYC3'">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create server') }}</button>
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
                <h5 class="modal-title" id="setupModalLabel">{{ __('Server Setup') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ __('To install your server:') }}<br>
                <ul>
                    <li>{{ __('Use a clean VPS with Ubuntu Server 18.04') }}</li>
                    <li>{{ __('Login into your VPS via SSH (as root):') }}<br>
                        <code><i>ssh root@<span class="server-ip"></span></i></code>
                    </li>
                    <li>{{ __('Run this command:') }}<br>
                        <code><i>wget -O - {{ url('/scripts/install') }}/<span class="server-id"></span>/ | bash</i></code>
                    <li>{{ __('Before you can use Cipi, please make sure your server is an Ubuntu 18.04 x86_64 LTS (Fresh installation)') }}</li>
                    <li>{{ __('Hardware Requirement: More than 1GB of HDD / At least 1 core processor / 512MB minimum RAM / At least 1 public IP Address (NAT VPS is not supported)') }}</li>
                    <li>{{ __('Please open port 22, 80 and 443 to install Cipi') }}</li>
                    <li>{{ __('Cipi would not work with NAT VPN and OpenVZ.') }}</li> 
                    <li>{{ __('Installation may take up to about 5 minutes minimum which may also depend on your server internet speed. After the installation is completed, you are ready to use Cipi to manage your servers.') }}</li>
                    <li>{{ __('AWS by default disables root login. To login as root inside AWS, login as default user and then use command sudo -s:') }}<br>
                        <code>
                            $ ssh ubuntu@ your server IP address
                            $ ubuntu@aws:~$ sudo -s
                            $ root@aws:~# paste installation script
                        </code>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>


<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('serverdelete') }}" method="POST">
                @csrf
                <input type="hidden" name="servercode" id="server-code" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('Delete server') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h4>{{ __('Are you sure to delete server') }} <b><span id="server-name"></span></b>?</h4>
                            <div style="min-height: 20px;"></div>
                            <select class="form-control" name="server_id" required id="server-list">
                                <option value="">{{ __('Select...') }}</option>
                                <option value="">{{ __('YES! I\'m shure!!!') }}</option>
                            </select>
                            <div style="min-height: 20px;"></div>
                            <h6 class="text-danger">{{ __('You can not reverse this action.') }}</h6>
                            <h6 class="text-danger">{{ __('You will loose control on this server') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Delete server') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
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
</script>
@endsection
