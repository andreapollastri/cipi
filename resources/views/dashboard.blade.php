@extends('layouts.app')



@section('title')
Dashboard
@endsection



@section('content')
@if(count($servers) > 0)
<div class="row">
    <div class="col">
        <a href="#" class="btn btn-sm btn-primary shadow-sm float-right">
            <i class="fas fa-recycle fa-sm text-white-50"></i> CHECK NOW
        </a>
    </div>
</div>
@else
@endif

<div class="space"></div>

<div class="row">
    <div class="col">
    @if(count($servers) > 0)
        @foreach($servers as $server)
        <div class="row server-card" data-id="{{ $server->servercode }}">
            <div class="col-sm-12 mb-4">
                <div class="card border-left-default shadow h-100 py-2" id="ping-{{ $server->servercode }}">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 d-none d-lg-block">Server</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800 mb-1">
                                    {{ $server->name }}
                                </div>
                            </div>
                            <div class="col mr-2 d-none d-xl-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">APPS</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center">
                                    {{ count($server->applications) }}
                                </div>
                            </div>
                            <div class="col mr-2 d-none d-lg-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">CPU</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="cpu-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col mr-2 d-none d-lg-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">RAM</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="ram-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col mr-2 d-none d-lg-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">HDD</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="hdd-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col-auto">
                                <a href="/server/{{ $server->servercode }}"><i class="fas fa-arrow-circle-right fa-2x text-gray-300"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-sm-12 text-center">
            <div class="space"></div>
            <div class="space"></div>
            <div class="space"></div>
            <i class="fab fa-linux fa-10x"></i>
            <h4>There's nothing here, yet!</h4>
            <div class="space"></div>
            <a href="/servers" class="btn btn-primary">ADD A SERVER NOW!</a>
            <div class="space"></div>
        </div>
    @endif
    </div>
</div>

@endsection



@section('extra')

@endsection



@section('css')

@endsection



@section('js')
<script>
    function statusdetail(serverid) {
        $.get("/remote/status/"+serverid, function(status) {
            status = status.split(";");
            $("#cpu-"+serverid).text(status[0]);
            $("#ram-"+serverid).text(status[1]);
            $("#hdd-"+serverid).text(status[2]);
        });
    }
    function statusping(serverid) {
        $.ajax({
            url: "/remote/ping/"+serverid,
            type: "GET",
            success: function(ping){
                if(ping != 200) {
                    $("#ping-"+serverid).removeClass("border-left-default");
                    $("#ping-"+serverid).removeClass("border-left-success");
                    $("#ping-"+serverid).addClass("border-left-danger");
                } else {
                    $("#ping-"+serverid).removeClass("border-left-default");
                    $("#ping-"+serverid).removeClass("border-left-danger");
                    $("#ping-"+serverid).addClass("border-left-success");
                }
            },
            error: function(ping) {
                $("#ping-"+serverid).removeClass("border-left-default");
                $("#ping-"+serverid).removeClass("border-left-success");
                $("#ping-"+serverid).addClass("border-left-danger");
            }
        });
    }
    function statuscheck() {
        $(".server-card").each(function() {
            serverid = $(this).attr("data-id");
            $("#cpu-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            $("#ram-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            $("#hdd-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            statusdetail(serverid);
            statusping(serverid);
        });
    }
    //START SCRIPT
    window.onload = function() {
        statuscheck();
    };
    //MANUAL CHECK
    $("#statuscheck").click(function() {
        statuscheck();
    });
    //AUTO CHECK
    setInterval(function() {
        statuscheck();
    }, 7500);
</script>
@endsection
