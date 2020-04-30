@extends('layouts.app')



@section('title')
{{ $app["domain"] }}
@endsection



@section('content')
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                <i class="fas fa-lock"></i> SSH/SFTP
            </div>
            <div class="card-body">
                <div class="space"></div>
                <b>Host</b> {{ $app["host"] }}
                <div class="space"></div>
                <b>Port</b> {{ $app["port"] }}
                <div class="space"></div>
                <b>User</b> {{ $app["user"] }}<br>
                <div class="space"></div>
                <b>Pass</b> {{ $app["pass"] }}<br>
                <div class="space"></div>
                <b>Path</b> /home/{{ $app["user"] }}/web/{{ $app["path"] }}<br>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card sm-12">
            <div class="card-header text-center">
                <i class="fas fa-database"></i> MySQL
            </div>
            <div class="card-body">
                <div class="space"></div>
                <b>Host</b> 127.0.0.1
                <div class="space"></div>
                <b>Port</b> 3306
                <div class="space"></div>
                <b>User</b> {{ $app["dbuser"] }}<br>
                <div class="space"></div>
                <b>Pass</b> {{ $app["dbpass"] }}<br>
                <div class="space"></div>
                <b>Name</b> {{ $app["dbname"] }}
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 text-center">
        <div class="space"></div>
            <p>Your application <i>{{ $app["domain"] }}</i> is PHP {{ $app["php"] }} based!</p>
        <div class="space"></div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 text-center">
        <div class="space"></div>
            <a href="/applications" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Application list</a>
            <a href="/application/pdf/{{ $appcode }}" class="btn btn-primary btn-sm" style="margin-left: 20px;"><i class="fas fa-file-pdf"></i> Download PDF</a>
        <div class="space"></div>
    </div>
</div>
@endsection



@section('extra')

@endsection



@section('css')

@endsection



@section('js')

@endsection
