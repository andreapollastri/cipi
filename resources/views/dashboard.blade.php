@extends('layouts.app')

@section('content')

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Dashboard') }}</h1>
        @if(count($servers) > 0)
            <a href="#" class="btn btn-sm btn-secondary shadow-sm" id="statuscheck"><i class="fas fa-recycle"></i> {{ __('CHECK NOW') }}</a>
        @endif
    </div>

    @if(count($servers) > 0)
        @foreach($servers as $server)
        <div class="row server-card" data-id="{{ $server->servercode }}">
            <div class="col-sm-12 mb-4">
                <div class="card border-left-default shadow h-100 py-2" id="ping-{{ $server->servercode }}">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 d-none d-xl-block">{{ __('Server') }}</div>
                                <div class="h4 mb-0 font-weight-bold text-gray-800 mb-1">
                                    {{ $server->name }}
                                </div>
                            </div>
                            <div class="col mr-2 d-none d-xl-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('APPS') }}</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center">
                                    {{ count($server->applications) }}
                                </div>
                            </div>
                            <div class="col mr-2 d-none d-xl-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('CPU') }}</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="cpu-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col mr-2 d-none d-xl-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('RAM') }}</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="ram-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col mr-2 d-none d-xl-block">
                                <div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('HDD') }}</div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="hdd-{{ $server->servercode }}"><i class="fas fa-spinner fa-spin"></i></div>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('server', $server->servercode) }}"><i class="fas fa-arrow-circle-right fa-2x text-gray-300"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else 
        <div class="col-sm-12 text-center">
            <div style="min-height: 20px"></div>
            <i class="fab fa-linux fa-10x"></i>
            <h4>{{ __('There\'s nothing here, yet!') }}</h4>
            <div style="min-height: 30px"></div>
            <a href="{{ route('servers') }}" class="btn btn-primary">{{ __('ADD A SERVER NOW!') }}</a>
            <div style="min-height: 20px"></div>
        </div>
    @endif


@endsection

@section('scripts')
<script>
    function statusdetail(serverid) {
        $.get("{{ url('/') }}/server/api/status/"+serverid, function(status) {
            status = status.split(";");
            $("#cpu-"+serverid).text(status[0]);
            $("#ram-"+serverid).text(status[1]);
            $("#hdd-"+serverid).text(status[2]);
        });
    }

    function statusping(serverid) {
        $.ajax({
            url: "{{ url('/') }}/server/api/ping/"+serverid,
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

