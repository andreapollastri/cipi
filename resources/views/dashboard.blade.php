@extends('template')


@section('title')
    {{ __('cipi.titles.dashboard') }}
@endsection



@section('content')
<div id="dashboard"></div>
@endsection



@section('extra')

@endsection



@section('css')

@endsection



@section('js')
<script>
// Loading
$('#mainloading').removeClass('d-none');

// Get Servers
count=0;
$.ajax({
    type: 'GET',
    url: '/api/servers',
    success: function(data) {
        $('#mainloading').addClass('d-none');
        data.forEach(server => {
            if(server.status > 0) {
                $.ajax({
                    type: 'GET',
                    url: '/api/servers/'+server.server_id+'/healthy',
                    beforeSend: function() {
                        $('#ram-'+server.server_id).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                        $('#cpu-'+server.server_id).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                        $('#hdd-'+server.server_id).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                    },
                    success: function(data) {
                        $('#ram-'+server.server_id).html(data.ram+'%');
                        $('#cpu-'+server.server_id).html(data.cpu+'%');
                        $('#hdd-'+server.server_id).html(data.hdd+'%');
                    }
                });
                $('#dashboard').append('<div class="row servercard" serverid="'+server.server_id+'"><div class="col-sm-12 mb-4"><div class="card border-left-default shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-default text-uppercase mb-1 d-none d-lg-block">{{ __('cipi.server') }}</div><div class="h4 mb-0 font-weight-bold text-gray-800 mb-1">'+server.name+'</div></div><div class="col mr-2 d-none d-xl-block"><div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('cipi.sites') }}</div><div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center">'+server.sites+'</div></div><div class="col mr-2 d-none d-lg-block"><div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('cipi.cpu') }}</div><div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="cpu-'+server.server_id+'"><i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i></div></div><div class="col mr-2 d-none d-lg-block"><div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('cipi.ram') }}</div><div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="ram-'+server.server_id+'"><i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i></div></div><div class="col mr-2 d-none d-lg-block"><div class="text-xs font-weight-bold text-default text-uppercase mb-1 text-center">{{ __('cipi.hdd') }}</div><div class="h6 mb-0 font-weight-bold text-gray-800 mb-1 text-center" id="hdd-'+server.server_id+'"><i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i></div></div><div class="col-auto"><a href="/servers/'+server.server_id+'" title="{{ __('cipi.manage') }}"><i class="fas fa-arrow-circle-right fa-2x text-gray-300" id="ping-'+server.server_id+'"></i></a></div></div></div></div></div></div>');
                count=count+1;
            }
        });
        if(count = 0) {
            $('#dashboard').html('<div class="col-sm-12 text-center"><div class="space"></div><div class="space"></div><div class="space"></div><i class="fab fa-linux fa-10x"></i><h4>{{ __('cipi.no_results_found') }}</h4><div class="space"></div><a href="/servers" class="btn btn-primary">{{ __('cipi.add_new_server') }}!</a><div class="space"></div></div>');
        }
    }
});


//Refresh Servers Status
setInterval(function() {
    $('.servercard').each(function(server) {
        var thisserverping = $(this).attr('serverid');
        (function(thisserverping){
            $.ajax({
                type: 'GET',
                url: '/api/servers/'+thisserverping+'/ping',
                beforeSend: function() {
                    $('#ping-'+thisserverping).removeClass('text-primary');
                    $('#ping-'+thisserverping).removeClass('fa-arrow-circle-right');
                    $('#ping-'+thisserverping).addClass('text-secondary');
                    $('#ping-'+thisserverping).addClass('fa-spinner');
                    $('#ping-'+thisserverping).addClass('fa-spin');
                },
                success: function(data) {
                    $('#ping-'+thisserverping).removeClass('text-secondary');
                    $('#ping-'+thisserverping).removeClass('fa-spinner');
                    $('#ping-'+thisserverping).removeClass('fa-spin');
                    $('#ping-'+thisserverping).addClass('text-primary');
                    $('#ping-'+thisserverping).addClass('fa-arrow-circle-right');
                }
            });
        })(thisserverping);
    });
}, 10000);

//Refresh Servers Status
setInterval(function() {
    $('.servercard').each(function(server) {
        var thisserverstatus = $(this).attr('serverid');
        (function(thisserverstatus){
            $.ajax({
                type: 'GET',
                url: '/api/servers/'+thisserverstatus+'/healthy',
                beforeSend: function() {
                    $('#ram-'+thisserverstatus).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                    $('#cpu-'+thisserverstatus).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                    $('#hdd-'+thisserverstatus).html('<i class="fas fa-spinner fa-spin" title="{{ __('cipi.loading_please_wait') }}"></i>');
                },
                success: function(data) {
                    $('#ram-'+thisserverstatus).html(data.ram+'%');
                    $('#cpu-'+thisserverstatus).html(data.cpu+'%');
                    $('#hdd-'+thisserverstatus).html(data.hdd+'%');
                }
            });
        })(thisserverstatus);
    });
}, 30000);

</script>


@endsection
