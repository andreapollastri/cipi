@extends('template')

@section('title')Dashboard @endsection

@section('css')

@endsection

@section('content')
<div class="col-xs-12">
    <div class="row">
        <div class="col-xs-10">
            <h4><i class="fas fa-tachometer-alt"></i> dashboard</h4>
        </div>
        <div class="col-xs-2 text-right">
            <h4><a href="javascript:void(null)"><i class="fas fa-sync" id="reload"></i></a></h4>
        </div>
    </div>
    <div class="space"></div>

    <!-- LOADING -->
    <div id="dashbox-loading" class="text-center">
        <div class="space"></div>
        <div class="space"></div>
        <div class="space"></div>
        <h1><i class="fas fa-circle-notch fa-spin"></i></h1>
    </div>
    <!-- LOADING -->

    <!-- DASHBOX -->
    <div class="col-md-6 col-lg-4" id="dashbox" style="display:none;">
        <div class="dashbox" data-id="" data-ip="">
            <div class="row">
                <div class="col-xs-12">
                    <div class="row">
                        <div class="col-xs-10">
                            <h4 class="limitch upper"><b class="dashbox-title"></b></h4>
                        </div>
                        <div class="col-xs-2 text-right">
                            <i class="dashbox-status fas fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="dashbox-hr"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <i class="fas fa-network-wired" style="font-size:12px"></i> <span class="dashbox-ip"></span>
                </div>
                <div class="col-xs-6 text-right">
                    <i class="dashbox-provider"></i> <span class="dashbox-location upper"></span>
                </div>
            </div>
            <div class="space"></div>
            <div class="row">
                <div class="col-xs-4 text-center dashbox-stats dashbox-rb">
                    CPU
                    <span class="dashbox-cpu fas fa-microchip fa-spin"></span>
                </div>
                <div class="col-xs-4 text-center dashbox-stats dashbox-rb">
                    RAM
                    <span class="dashbox-ram fas fa-memory fa-spin"></span>
                </div>
                <div class="col-xs-4 text-center dashbox-stats">
                    HDD
                    <span class="dashbox-hdd fas fa-hdd fa-spin"></span>
                </div>
            </div>
        </div>
    </div>
    <!-- DASHBOX -->

    <div class="row" id="boxes"></div>

</div>
@endsection


@section('js')
<script>

    function loadinfo() {
        $('#boxes').empty();
        $('#dashbox-loading').show();
        $.ajax({
            url: "/cloud/api",
            type: "GET",
            success: function(data) {
                $('#dashbox-loading').hide();
                data.forEach(item => {
                    var box = $("#dashbox").clone();
                    box.attr('id','dashbox-'+item.id);
                    box.addClass('dashbox-checkable');
                    box.find('.dashbox').attr('data-id',item.code);
                    box.find('.dashbox').attr('data-ip',item.ip);
                    box.find('.dashbox-title').append(item.name);
                    box.find('.dashbox-ip').append(item.ip);
                    box.find('.dashbox-provider').append(cloudicon(item.provider));
                    box.find('.dashbox-location').append(item.location);
                    $('#boxes').append(box);
                    statuscheck();
                    box.fadeIn();
                });
            },
            error: function(data) {
                console.log(data);
                alert('Internal server error! Retry!');
            }
        });
    }

    function statuscheck() {
        $('.dashbox-checkable').each(function() {
            var item = '#'+$(this).attr('id');
            $(item).find('.dashbox-status').removeClass('red green');
            $(item).find('.dashbox-status').empty();
            $(item).find('.dashbox-status').addClass('fas fa-circle-notch fa-spin');
            $(item).find('.dashbox-cpu').removeClass('fas fa-unlink');
            $(item).find('.dashbox-cpu').empty();
            $(item).find('.dashbox-cpu').addClass('fas fa-microchip fa-spin');
            $(item).find('.dashbox-ram').removeClass('fas fa-unlink');
            $(item).find('.dashbox-ram').empty();
            $(item).find('.dashbox-ram').addClass('fas fa-memory fa-spin');
            $(item).find('.dashbox-hdd').removeClass('fas fa-unlink');
            $(item).find('.dashbox-hdd').empty();
            $(item).find('.dashbox-hdd').addClass('fas fa-hdd fa-spin');
            $.ajax({
                url: 'http://'+$(item).find('.dashbox').attr('data-ip')+'/check-'+$(item).find('.dashbox').attr('data-id')+'.php',
                type: 'GET',
                success: function(data) {
                    $(item).find('.dashbox-status').removeClass('fas fa-circle-notch fa-spin');
                    $(item).find('.dashbox-status').addClass('fas fa-circle green');
                    $(item).find('.dashbox-cpu').removeClass('fas fa-microchip fa-spin');
                    $(item).find('.dashbox-cpu').html(data.cpu);
                    $(item).find('.dashbox-ram').removeClass('fas fa-memory fa-spin');
                    $(item).find('.dashbox-ram').html(data.ram);
                    $(item).find('.dashbox-hdd').removeClass('fas fa-hdd fa-spin');
                    $(item).find('.dashbox-hdd').html(data.hdd);
                },
                error: function(data) {
                    $(item).find('.dashbox-status').removeClass('fas fa-circle-notch fa-spin');
                    $(item).find('.dashbox-status').addClass('fas fa-circle red');
                    $(item).find('.dashbox-cpu').removeClass('fas fa-microchip fa-spin');
                    $(item).find('.dashbox-cpu').addClass('fas fa-unlink');
                    $(item).find('.dashbox-ram').removeClass('fas fa-memory fa-spin');
                    $(item).find('.dashbox-ram').addClass('fas fa-unlink');
                    $(item).find('.dashbox-hdd').removeClass('fas fa-hdd fa-spin');
                    $(item).find('.dashbox-hdd').addClass('fas fa-unlink');
                }
            });
        });
    }

    $(document).ready(function() {
        loadinfo();
    });

    $('#reload').click(function() {
        loadinfo();
    });

    $(document).on('click', '.dashbox', function() {
        window.location.href = '/cloud/'+$(this).attr('data-id');
    });

    setInterval(function() {
        statuscheck();
    }, 60000);

</script>
@endsection
