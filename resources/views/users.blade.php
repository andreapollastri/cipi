@extends('layouts.app')



@section('title')
Users
@endsection



@section('content')
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">User</th>
                        <th class="text-center d-none d-lg-table-cell">Application</th>
                        <th class="text-center">Server</th>
                        <th class="text-center d-none d-lg-table-cell">IP</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->username }}</td>
                        <td class="text-center">{{ $user->domain }}</td>
                        <td class="text-center">{{ $user->server->name }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $user->server->ip }}</td>
                        <td class="text-center">
                            <a href="#" data-toggle="modal" data-target="#resetModal" data-username="{{ $user->username }}">
                                <i class="fas fa-key" style="color:gray;"></i>
                            </a>
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
<!-- RESET -->
<div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Reset user</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                Are you sure to reset password for user:<br>
                <b><span class="ajax-user"></span></b>?<br><br>
                SSH/SFTP and MySQL passwords will be reset!<br><br>
                <form action="users/reset" method="POST">
                    @csrf
                    <input type="hidden" name="username" value="" class="ajax-username-form">
                    <input type="submit" class="btn btn-primary" value="Yes, continue!">
                </form>
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
$('#resetModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var user = button.data('username')
    var modal = $(this)
    modal.find('.ajax-user').text(user)
    modal.find('.ajax-username-form').val(user)
})
</script>
@endsection
