@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Users') }}</h1>  
</div>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('User') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('Application') }}</th>
                        <th class="text-center">{{ __('Server') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('IP') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hostusers as $hostuser)
                    <tr>
                        <td class="text-center">{{ $hostuser->username }}</td>
                        <td class="text-center">{{ $hostuser->domain }}</td>
                        <td class="text-center">{{ $hostuser->server->name }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $hostuser->server->ip }}</td>
                        <td class="text-center">
                            <a href="#" data-toggle="modal" data-target="#resetModal" data-username="{{ $hostuser->username }}">
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


<!-- RESET -->
<div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">{{ __('Reset user') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                {{ __('Are you shure to reset password for user') }}:<br>
                <b><span class="ajax-user"></span></b> ?<br><br>
                SSH/SFTP and MySQL passwords will be reset!<br><br>
                <form action="{{ route('usersreset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="username" value="" class="ajax-username-form"> 
                    <input type="submit" class="btn btn-primary" value="{{ __('Yes, continue!')}}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
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
$('#resetModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var user = button.data('username')
    var modal = $(this)
    modal.find('.ajax-user').text(user)
    modal.find('.ajax-username-form').val(user)
})
</script>
@endsection
