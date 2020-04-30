@extends('layouts.app')



@section('title')
Applications
@endsection



@section('content')
<div class="row">
    <div class="col">
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
                        <th class="text-center">Server</th>
                        <th class="text-center">User</th>
                        <th class="text-center">PHP</th>
                        <th class="text-center">Aliases</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($applications as $application)
                    <tr>
                        <td class="text-center">{{ $application->domain }}</td>
                        <td class="text-center">{{ $application->server->ip }}</td>
                        <td class="text-center">{{ $application->username }}</td>
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
@endsection



@section('extra')

@endsection



@section('css')

@endsection



@section('js')

@endsection
