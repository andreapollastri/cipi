@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Aliases') }}</h1>
    <a href="#" class="btn btn-sm btn-secondary shadow-sm " data-toggle="modal" data-target="#createModal" ><i class="fas fa-plus"></i><span class="d-none d-md-inline"> {{ __('CREATE NEW') }}</span></a> 
</div>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('Alias') }}</th>
                        <th class="text-center">{{ __('Application') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('IP') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($aliases as $alias)
                    <tr>
                        <td class="text-center">{{ $alias->domain }}</td>
                        <td class="text-center">{{ $alias->application->domain }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $alias->server->ip }}</td>
                        <td class="text-center">
                            <i class="fab fa-expeditedssl ssl-click" style="margin-right: 18px; cursor: pointer; color: gray;" data-alias="{{ $alias->aliascode }}" id="ssl-{{ $alias->aliascode }}"></i>
                            <i data-toggle="modal" data-target="#deleteModal" class="fas fa-trash-alt" data-alias-code="{{ $alias->aliascode }}" data-alias-domain="{{ $alias->domain }}" data-alias-parentdomain="{{ $alias->application->domain }}" style="color:gray; cursor: pointer;"></i>
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
            <form action="{{ route('aliascreate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">{{ __('Create a new alias') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="domain" class="col-md-4 col-form-label text-md-right">{{ __('Alias domain') }}*</label>
                        <div class="col-md-6">
                            <input id="domain" type="text" class="form-control @error('domain') is-invalid @enderror" name="domain" required autocomplete="off" autofocus placeholder="E.g. 'yourdomain.ltd'">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="server_id" class="col-md-4 col-form-label text-md-right">{{ __('Server') }}*</label>
                        <div class="col-md-6">
                            <select class="form-control" name="server_id" required id="server-list">
                                <option value="">{{ __('Select...') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="server_id" class="col-md-4 col-form-label text-md-right">{{ __('Application') }}*</label>
                        <div class="col-md-6">
                            <select class="form-control" name="application_id" required id="application-list">
                                <option value="">{{ __('Select...') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create alias') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('aliasdelete') }}" method="POST">
                @csrf
                <input type="hidden" name="aliascode" id="alias-code" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('Delete alias') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h6>{{ __('Are you sure to delete alias') }} <i><b><span id="alias-domain"></span></b></i> {{ __('related to application') }} <i><span id="alias-parentdomain"></span></i>?</h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Delete alias') }}</button>
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
    $.get("{{ url('/') }}/ajaxservers/", function(servers) { 
        JSON.parse(servers).forEach(server => {
            $("#server-list").append("<option value='"+server["id"]+"'>"+server["name"]+" ("+server["ip"]+")</option>");
        });
    });
</script>
<script>
function getapplications(server) {
    $("#application-list").empty();
    $("#application-list").append("<option value=''>Select...</option>");
    $.get("{{ url('/') }}/ajaxapplications/"+server, function(applications) { 
        JSON.parse(applications).forEach(application => {
            $("#application-list").append("<option value='"+application["id"]+"'>"+application["domain"]+"</option>");
        });
    });
}
$("#server-list").change(function() {
    getapplications($(this).val());
});
</script>
<script>
function generatessl(alias) {
    console.log(alias);
    $("#ssl-"+alias).removeClass("fab fa-expeditedssl");
    $("#ssl-"+alias).addClass("fas fa-spinner fa-spin");
    $.ajax({
        url: "{{ url('/') }}/server/api/sslalias/"+alias,
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
    var aliascode = button.data('alias-code')
    var aliasdomain = button.data('alias-domain')
    var aliasparentdomain = button.data('alias-parentdomain')
    var modal = $(this)
    modal.find('#alias-domain').text(aliasdomain)
    modal.find('#alias-parentdomain').text(aliasparentdomain)
    modal.find('#alias-code').val(aliascode)
})
</script>
@endsection
