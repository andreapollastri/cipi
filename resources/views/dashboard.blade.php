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
            <h4><i class="fas fa-sync"></i></h4>
        </div>
    </div>
    <div class="space"></div>
    <div class="row" id="boxes">

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
            <div class="dashbox">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-10">
                                <h4><b class="dashbox-title"></b></h4>
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
                    <div class="dashbox-ip col-xs-6"></div>
                    <div class="col-xs-6 text-right">
                        <i class="dashbox-provider"></i> <span class="dashbox-location"></span>
                    </div>
                </div>
                <div class="space"></div>
                <div class="row">
                    <div class="col-xs-4 text-center dashbox-stats dashbox-rb">
                        CPU
                        <span class="dashbox-cpu fas fa-circle-notch fa-spin"></span>
                    </div>
                    <div class="col-xs-4 text-center dashbox-stats dashbox-rb">
                        RAM
                        <span class="dashbox-ram fas fa-circle-notch fa-spin"></span>
                    </div>
                    <div class="col-xs-4 text-center dashbox-stats">
                        HDD
                        <span class="dashbox-hdd fas fa-circle-notch fa-spin"></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- DASHBOX -->

    </div>
</div>
@endsection

@section('modals')

@endsection


@section('js')
<script>
    $.ajax({
        url: "/cloud/api/list/",
        type: "GET",
        success: function(data) {
            $('#dashbox-loading').hide();
            data.forEach(item => {
                var box = $("#dashbox").clone();
                box.attr('id',item.code);
                box.find('.dashbox-title').append(item.name);
                box.find('.dashbox-ip').append(item.ip);
                box.find('.dashbox-location').append(item.location);
                $('#boxes').append(box);
                box.fadeIn();
            });
        },
        error: function(data) {
            console.log(data);
            alert('Internal server error! Retry!');
        }
    });
</script>
@endsection
