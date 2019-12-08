@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Databases') }}</h1>  
</div>
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('Database') }}</th>
                        <th class="text-center">{{ __('Domain') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('Server') }}</th>
                        <th class="text-center d-none d-lg-table-cell">{{ __('IP') }}</th>
                        <th class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($databases as $database)
                    <tr>
                        <td class="text-center">{{ $database->username }}</td>
                        <td class="text-center">{{ $database->domain }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $database->server->name }}</td>
                        <td class="text-center d-none d-lg-table-cell">{{ $database->server->ip }}</td>
                        <td class="text-center">
                        	<a href="http://{{ $database->server->ip }}/phpmyadmin/" target="_blank">
                        		<i class="fas fa-table" style="color:gray;"></i>
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

@section('scripts')
<link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
	$(document).ready(function() {
		$('#dataTable').DataTable();
	});
</script>
@endsection
