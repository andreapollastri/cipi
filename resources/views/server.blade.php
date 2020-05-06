@extends('layouts.app')



@section('title')
{{ $server->name }}
@endsection



@section('content')
<div class="row">
    <div class="col">
        <span style="margin-right: 18px"></span>
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> CREATE APP
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
                        <th class="text-center">Domain</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Path</th>
                        <th class="text-center">PHP</th>
                        <th class="text-center">Aliases</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($server->applications as $application)
                    <tr>
                        <td class="text-center">{{ $application->domain }}</td>
                        <td class="text-center">{{ $application->username }}</td>
                        <td class="text-center">/{{ $application->basepath }}</td>
                        <td class="text-center">{{ $application->php }}</td>
                        <td class="text-center">{{ count($application->aliases) }}</td>
                        <td class="text-center">
                            <i class="fab fa-expeditedssl ssl-click" style="margin-right: 18px; cursor: pointer; color: gray;" data-application="{{ $application->appcode }}" id="ssl-{{ $application->appcode }}"></i>
                            <i class="fas fa-trash-alt" data-toggle="modal" data-target="#deleteModal" class="fas fa-trash-alt" data-app-code="{{ $application->appcode }}" data-app-domain="{{ $application->domain }}" style="color:gray; cursor: pointer;"></i>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col">
        <span id="server-id" servercode="{{ $server->servercode }}"></span>
        <span id="reset-supervisor" style="margin-right: 10px; font-size: 10px;" class="btn btn-sm btn-primary shadow-sm float-right">
            <i id="reset-supervisor-icon" class="d-none d-md-inline fas fa-redo fa-sm text-white-50"></i> SUPERVISOR
        </span>
        <span id="reset-redis" style="margin-right: 10px; font-size: 10px;" class="btn btn-sm btn-primary shadow-sm float-right">
            <i id="reset-redis-icon" class="d-none d-md-inline fas fa-redo fa-sm text-white-50"></i> REDIS
        </span>
        <span id="reset-mysql" style="margin-right: 10px; font-size: 10px;" class="btn btn-sm btn-primary shadow-sm float-right">
            <i id="reset-mysql-icon" class="d-none d-md-inline fas fa-redo fa-sm text-white-50"></i> MYSQL
        </span>
        <span id="reset-php" style="margin-right: 10px; font-size: 10px;" class="btn btn-sm btn-primary shadow-sm float-right">
            <i id="reset-php-icon" class="d-none d-md-inline fas fa-redo fa-sm text-white-50"></i> PHP
        </span>
        <span id="reset-nginx" style="margin-right: 10px; font-size: 10px;" class="btn btn-sm btn-primary shadow-sm float-right">
            <i id="reset-nginx-icon" class="d-none d-md-inline fas fa-redo fa-sm text-white-50"></i> NGINX
        </span>
        <span style="margin-right: 10px; font-size: 10px;" class="float-right d-none d-sm-inline">RESTART SERVICES</span>
    </div>
</div>
@endsection



@section('extra')
<!-- CREATE -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/application/create" method="POST" id="form-app-create" class="ws-validate">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create a new application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="domain" class="col-md-4 col-form-label text-md-right">Domain name *</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="domain" type="text" class="form-control" name="domain" required autocomplete="off" placeholder="E.g. 'yourdomain.ltd'">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="server_id" class="col-md-4 col-form-label text-md-right">Server</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <select class="form-control" name="server_id" required id="server-list"></select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="basepath" class="col-md-4 col-form-label text-md-right">Basepath</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input id="basepath" type="text" class="form-control" name="basepath" autocomplete="off" placeholder="E.g. 'public'" value="public">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="php" class="col-md-4 col-form-label text-md-right">PHP</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <select class="form-control" name="php">
                                    <option value="7.4" selected>7.4</option>
                                    <option value="7.3">7.3</option>
                                    <option value="7.2">7.2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="app-close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="app-create">Create application</button>
                    <div id="app-coming" style="display: none;"><i class="fas fa-spinner fa-spin"></i>  <b>Your app is coming... Hold On!!!</b></div>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/application/destroy" method="POST">
                @csrf
                <input type="hidden" name="appcode" id="app-code" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete application</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h6>Are you sure to delete application <i><b><span id="app-domain"></span></b></i>, its database and all realated aliases?</h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Delete application</button>
                </div>
            </form>
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
    $.get("/servers/api", function(servers) {
        JSON.parse(JSON.stringify(servers)).forEach(server => {
            if(server["id"] == {{ $server->id }}) {
                $("#server-list").append("<option value='"+server["id"]+"' selected>"+server["name"]+" ("+server["ip"]+")</option>");
            } else {
                $("#server-list").append("<option value='"+server["id"]+"'>"+server["name"]+" ("+server["ip"]+")</option>");
            }
        });
    });
</script>
<script>
function generatessl(application) {
    $("#ssl-"+application).removeClass("fab fa-expeditedssl");
    $("#ssl-"+application).addClass("fas fa-spinner fa-spin");
    $.ajax({
        url: "/application/ssl/"+application,
        type: "GET",
        success: function(response){
            if(response != "OK") {
                $("#ssl-"+application).removeClass("fa-spinner fa-spin");
                $("#ssl-"+application).removeClass("ssl-click");
                $("#ssl-"+application).removeClass("text-success");
                $("#ssl-"+application).addClass("text-danger");
                $("#ssl-"+application).addClass("fa-times");
            } else {
                $("#ssl-"+application).removeClass("fa-spinner fa-spin");
                $("#ssl-"+application).removeClass("ssl-click");
                $("#ssl-"+application).removeClass("text-danger");
                $("#ssl-"+application).addClass("text-success");
                $("#ssl-"+application).addClass("fa-check");
            }
        },
        error: function(response) {
            $("#ssl-"+application).removeClass("fa-spinner fa-spin");
            $("#ssl-"+application).removeClass("ssl-click");
            $("#ssl-"+application).removeClass("text-success");
            $("#ssl-"+application).addClass("text-danger");
            $("#ssl-"+application).addClass("fa-times");
        }
    });
}
$(".ssl-click").click(function() {
    generatessl($(this).attr("data-application"));
});
</script>
<script>
$('#deleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var appcode = button.data('app-code')
    var appdomain = button.data('app-domain')
    var modal = $(this)
    modal.find('#app-domain').text(appdomain)
    modal.find('#app-code').val(appcode)
})
</script>
<script>
    $("#form-app-create").submit(function() {
        $("#app-create").hide();
        $("#app-close").hide();
        $("#app-coming").show();
    });
</script>
<script>
    server = $('#server-id').attr('servercode');
    $("#reset-nginx").click(function() {
        $("#reset-nginx-icon").removeClass("fas fa-redo");
        $("#reset-nginx-icon").addClass("fas fa-spinner fa-spin");
        $.ajax({
            url: "/server/nginx/"+server,
            type: "GET",
            success: function(response){
                $("#reset-nginx-icon").removeClass("fa-spinner fa-spin");
                $("#reset-nginx-icon").addClass("fa-redo");
            },
            error: function(response) {
                $("#reset-nginx-icon").removeClass("fa-spinner fa-spin");
                $("#reset-nginx-icon").addClass("fa-times");
            }
        });
    });
    $("#reset-php").click(function() {
        $("#reset-php-icon").removeClass("fas fa-redo");
        $("#reset-php-icon").addClass("fas fa-spinner fa-spin");
        $.ajax({
            url: "/server/php/"+server,
            type: "GET",
            success: function(response){
                $("#reset-php-icon").removeClass("fa-spinner fa-spin");
                $("#reset-php-icon").addClass("fa-redo");
            },
            error: function(response) {
                $("#reset-php-icon").removeClass("fa-spinner fa-spin");
                $("#reset-php-icon").addClass("fa-times");
            }
        });
    });
    $("#reset-mysql").click(function() {
        $("#reset-mysql-icon").removeClass("fas fa-redo");
        $("#reset-mysql-icon").addClass("fas fa-spinner fa-spin");
        $.ajax({
            url: "/server/mysql/"+server,
            type: "GET",
            success: function(response){
                $("#reset-mysql-icon").removeClass("fa-spinner fa-spin");
                $("#reset-mysql-icon").addClass("fa-redo");
            },
            error: function(response) {
                $("#reset-mysql-icon").removeClass("fa-spinner fa-spin");
                $("#reset-mysql-icon").addClass("fa-times");
            }
        });
    });
    $("#reset-redis").click(function() {
        $("#reset-redis-icon").removeClass("fas fa-redo");
        $("#reset-redis-icon").addClass("fas fa-spinner fa-spin");
        $.ajax({
            url: "/server/redis/"+server,
            type: "GET",
            success: function(response){
                $("#reset-redis-icon").removeClass("fa-spinner fa-spin");
                $("#reset-redis-icon").addClass("fa-redo");
            },
            error: function(response) {
                $("#reset-redis-icon").removeClass("fa-spinner fa-spin");
                $("#reset-redis-icon").addClass("fa-times");
            }
        });
    });
    $("#reset-supervisor").click(function() {
        $("#reset-supervisor-icon").removeClass("fas fa-redo");
        $("#reset-supervisor-icon").addClass("fas fa-spinner fa-spin");
        $.ajax({
            url: "/server/supervisor/"+server,
            type: "GET",
            success: function(response){
                $("#reset-supervisor-icon").removeClass("fa-spinner fa-spin");
                $("#reset-supervisor-icon").addClass("fa-redo");
            },
            error: function(response) {
                $("#reset-supervisor-icon").removeClass("fa-spinner fa-spin");
                $("#reset-supervisor-icon").addClass("fa-times");
            }
        });
    });
</script>
@endsection
