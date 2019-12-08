@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Applications') }}</h1>
    <a href="#" class="btn btn-sm btn-secondary shadow-sm " data-toggle="modal" data-target="#createModal" ><i class="fas fa-plus"></i><span class="d-none d-md-inline"> {{ __('CREATE NEW') }}</span></a>
</div>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('Domain') }}</th>
                        <th class="text-center">{{ __('User') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('Server') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('IP') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($applications as $application)
                    <tr>
                        <td class="text-center">{{ $application->domain }}</td>
                        <td class="text-center">{{ $application->username }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $application->server->name }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $application->server->ip }}</td>
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


<!-- CREATE -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('applicationcreate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">{{ __('Create a new application') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="domain" class="col-md-4 col-form-label text-md-right">{{ __('Domain name') }}*</label>
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
                        <label for="basepath" class="col-md-4 col-form-label text-md-right">{{ __('Basepath') }}*</label>
                        <div class="col-md-6">
                            <input id="basepath" type="text" class="form-control @error('name') is-invalid @enderror" name="basepath" autocomplete="off" required autofocus placeholder="E.g. 'public'" value="public">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="autoinstall" class="col-md-4 col-form-label text-md-right">{{ __('Autoinstall') }}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="autoinstall">
                                <option value="none">{{ __('None... just pure web!') }}</option>
                                <option value="laravel">{{ __('Install Laravel') }}</option>
                                <option value="wordpress">{{ __('Install Wordpress') }}</option>
                                <option value="git">{{ __('Install a GitHub project') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary" id="app-create">{{ __('Create application') }}</button>
                    <button type="button" class="btn btn-danger" id="app-coming" style="display: none;">{{ __('Application is coming... Hold On!') }}</button>
                </div>
                <script>
                    $("#app-create").click(function() {
                        $(this).hide();
                        $("#app-coming").show();
                    });
                </script>
            </form>
        </div>
    </div>
</div>

<!-- DELETE -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('applicationdelete') }}" method="POST">
                @csrf
                <input type="hidden" name="appcode" id="app-code" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('Delete application') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class=" row">
                        <div class="col-sm-12">
                            <h6>{{ __('Are you sure to delete application') }} <i><b><span id="app-domain"></span></b></i>, {{ __('its database and all realated aliases') }}?</h6>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Delete application') }}</button>
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
function generatessl(application) {
    $("#ssl-"+application).removeClass("fab fa-expeditedssl");
    $("#ssl-"+application).addClass("fas fa-spinner fa-spin");
    $.ajax({
        url: "{{ url('/') }}/server/api/sslapplication/"+application,
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
@endsection
