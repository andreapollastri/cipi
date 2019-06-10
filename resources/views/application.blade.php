@extends('layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Application') }} <i class="fas fa-rocket"></i> {{ $app["domain"] }}</h1>
</div>

<!-- Content -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                <i class="fas fa-lock"></i> {{ __('SSH/SFTP') }}
            </div>
            <div class="card-body">
                <div style="min-height: 15px"></div>
                <b>Host</b> {{ $app["host"] }}
                <div style="min-height: 15px"></div>
                <b>Port</b> {{ $app["port"] }}
                <div style="min-height: 15px"></div>
                <b>User</b> {{ $app["user"] }}<br>
                <div style="min-height: 15px"></div>
                <b>Pass</b> {{ $app["pass"] }}<br> 
                <div style="min-height: 15px"></div>
                <b>Path</b> /home/{{ $app["user"] }}/web/{{ $app["path"] }}/<br>
                <div style="min-height: 15px"></div> 
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                <i class="fas fa-database"></i> {{ __('MySQL') }}
            </div>
            <div class="card-body">
                <div style="min-height: 15px"></div>
                <b>Host</b> 127.0.0.1
                <div style="min-height: 15px"></div>
                <b>Port</b> 3306
                <div style="min-height: 15px"></div>
                <b>User</b> {{ $app["dbuser"] }}<br>
                <div style="min-height: 15px"></div>
                <b>Pass</b> {{ $app["dbpass"] }}<br> 
                <div style="min-height: 15px"></div>
                <b>Name</b> {{ $app["dbname"] }}
                <div style="min-height: 15px"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 text-center">
        <div style="min-height: 20px"></div>
            <a href="{{ route('applications') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Application list</a>
            <a href="{{ route('pdf', $appcode) }}" class="btn btn-primary btn-sm" style="margin-left: 20px;"><i class="fas fa-file-pdf"></i> Download PDF</a>
        <div style="min-height: 20px"></div>
    </div>    
</div>
@endsection
