@extends('layouts.app')

@section('content')

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Hey!') }}</h1>
    </div>

    <div class="col-sm-12 text-center">
        <div style="min-height: 20px"></div>
        <i class="fab fa-linux fa-10x"></i>
        <h4>{{ $messagge }}</h4>
        <div style="min-height: 20px"></div>
    </div>

@endsection

@section('scripts')
<script>
    function statuscheck() {
        $(".server-card").each(function() {

            //SERVER STATUS
            serverid = $(this).attr("data-id");
            $("#cpu-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            $("#ram-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            $("#hdd-"+serverid).html('<i class="fas fa-spinner fa-spin"></i>');
            $.get("{{ url('/') }}/server/api/status/"+serverid, function(status) {
                status = status.split(";");
                $("#cpu-"+serverid).text(status[0]);
                $("#ram-"+serverid).text(status[1]);
                $("#hdd-"+serverid).text(status[2]);
            });

            //SERVER PING
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

