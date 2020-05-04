@extends('layouts.app')



@section('title')
Aliases
@endsection



@section('content')
<div class="row">
    <div class="col">
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> NEW ALIAS
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
                        <th class="text-center">Application</th>
                        <th class="text-center">Server</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($aliases as $alias)
                    <tr>
                        <td class="text-center">{{ $alias->domain }}</td>
                        <td class="text-center">{{ $alias->application->domain }}</td>
                        <td class="text-center">{{ $alias->application->server->ip }}</td>
                        <td class="text-center">{{ $alias->application->username }}</td>
                        <td class="text-center">
                            <i class="fab fa-expeditedssl ssl-click" style="margin-right: 18px; cursor: pointer; color: gray;" data-alias="{{ $alias->aliascode }}" id="ssl-{{ $alias->aliascode }}"></i>
                            <i class="fas fa-trash-alt" data-toggle="modal" data-target="#deleteModal" class="fas fa-trash-alt" data-alias-id="{{ $alias->aliascode }}" data-alias-domain="{{ $alias->domain }}" style="color:gray; cursor: pointer;"></i>
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
            <form action="/alias/create" method="POST" id="form-app-create" class="ws-validate">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create a new alias</h5>
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
                        <label for="application_id" class="col-md-4 col-form-label text-md-right">Application</label>
                        <div class="col-md-6">
                            <div class="form-group">
                                <select class="form-control" name="application_id" required id="application-list">
                                    <option value="" selected>Select...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="app-close" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="app-create">Create alias</button>
                    <div id="app-coming" style="display: none;"><i class="fas fa-spinner fa-spin"></i>  <b>Your alias is coming... Hold On!!!</b></div>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/alias/destroy" method="POST">
                @csrf
                <input type="hidden" name="aliascode" id="alias-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete alias</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h6>Are you sure to delete alias <i><b><span id="alias-domain"></span></b></i>?</h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Delete alias</button>
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
    $.get("/applications/api", function(applications) {
        JSON.parse(JSON.stringify(applications)).forEach(application => {
            $("#application-list").append("<option value='"+application["id"]+"'>"+application["domain"]+" (user "+application["username"]+")</option>");
        });
    });
</script>
<script>
function generatessl(alias) {
    $("#ssl-"+alias).removeClass("fab fa-expeditedssl");
    $("#ssl-"+alias).addClass("fas fa-spinner fa-spin");
    $.ajax({
        url: "/alias/ssl/"+alias,
        type: "GET",
        success: function(response){
            if(response != "OK") {
                $("#ssl-"+alias).removeClass("fa-spinner fa-spin");
                $("#ssl-"+alias).removeClass("ssl-click");
                $("#ssl-"+alias).removeClass("text-success");
                $("#ssl-"+alias).addClass("text-danger");
                $("#ssl-"+alias).addClass("fa-times");
            } else {
                $("#ssl-"+alias).removeClass("fa-spinner fa-spin");
                $("#ssl-"+alias).removeClass("ssl-click");
                $("#ssl-"+alias).removeClass("text-danger");
                $("#ssl-"+alias).addClass("text-success");
                $("#ssl-"+alias).addClass("fa-check");
            }
        },
        error: function(response) {
            $("#ssl-"+alias).removeClass("fa-spinner fa-spin");
            $("#ssl-"+alias).removeClass("ssl-click");
            $("#ssl-"+alias).removeClass("text-success");
            $("#ssl-"+alias).addClass("text-danger");
            $("#ssl-"+alias).addClass("fa-times");
        }
    });
}
$(".ssl-click").click(function() {
    generatessl($(this).attr("data-alias"));
});
</script>
<script>
$('#deleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var alias = button.data('alias-id')
    var domain = button.data('alias-domain')
    var modal = $(this)
    modal.find('#alias-domain').text(domain)
    modal.find('#alias-id').val(alias)
})
</script>
<script>
    $("#form-app-create").submit(function() {
        $("#app-create").hide();
        $("#app-close").hide();
        $("#app-coming").show();
    });
</script>
@endsection
