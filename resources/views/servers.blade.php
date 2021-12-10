@extends('template')


@section('title')
    {{ __('cipi.titles.servers') }}
@endsection



@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header text-right">
                <button class="btn btn-sm btn-secondary" id="newServer">
                    <i class="fas fa-plus mr-1"></i><b>{{ __('cipi.new_button', ['type' => __('cipi.server')]) }}</b>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dt" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center d-none d-md-table-cell">{{ __('cipi.name') }}</th>
                                <th class="text-center">IP</th>
                                <th class="text-center d-none d-lg-table-cell">{{ __('cipi.provider') }}</th>
                                <th class="text-center d-none d-xl-table-cell">{{ __('cipi.location') }}</th>
                                <th class="text-center">{{ __('cipi.actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<div class="modal fade" id="newServerModal" tabindex="-1" role="dialog" aria-labelledby="newServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="newserverdialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newServerModalLabel">{{ __('cipi.create_server_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="newserverform">
                    <label for="newservername">{{ __('cipi.server_name') }}</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newservername" placeholder="e.g. Production" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverip">{{ __('cipi.server_ip') }}</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverip" placeholder="e.g. 123.45.67.89" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverprovider">{{ __('cipi.server_provider') }}</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverprovider" placeholder="e.g. Digital Ocean" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newserverlocation">{{ __('cipi.server_location') }}</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newserverlocation" placeholder="e.g. Amsterdam" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="submit">{{ __('cipi.confirm') }} <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                    </div>
                </div>
                <div id="newserverok" class="d-none">
                    <p><b>{{ __('cipi.server_setup_title') }}</b>
                    <ul>
                        <li>{!! __('cipi.server_setup_step1') !!}</li>
                        <li>{!! __('cipi.server_setup_step2') !!}<br>
                            <code><i>ssh root@<span id="newserverssh"></span></i></code></li>
                        <li>{!! __('cipi.server_setup_step3') !!}<br>
                            <code><i>wget -O - {{ URL::to('/sh/setup/') }}/<span id="newserverid"></span> | bash</i></code></li>
                        <li>{!! __('cipi.server_setup_step4') !!}</li>
                        <li>{!! __('cipi.server_setup_step5') !!}</li>
                        <li>{!! __('cipi.server_setup_step6') !!}</li>
                        <li>{!! __('cipi.server_setup_step7') !!}</li>
                        <li>{!! __('cipi.server_setup_step8') !!}</li>
                    </ul>
                    </p>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="installServerModal" tabindex="-1" role="dialog" aria-labelledby="installServerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installServerModalLabel">{{ __('cipi.server_setup') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><b>{{ __('cipi.server_setup_title') }}</b>
                    <ul>
                        <li>{!! __('cipi.server_setup_step1') !!}</li>
                        <li>{!! __('cipi.server_setup_step2') !!}<br>
                            <code><i>ssh root@<span id="installserverssh"></span></i></code></li>
                        <li>{!! __('cipi.server_setup_step3') !!}<br>
                            <code><i>wget -O - {{ URL::to('/sh/setup/') }}/<span id="installserverid"></span> | bash</i></code></li>
                        <li>{!! __('cipi.server_setup_step4') !!}</li>
                        <li>{!! __('cipi.server_setup_step5') !!}</li>
                        <li>{!! __('cipi.server_setup_step6') !!}</li>
                        <li>{!! __('cipi.server_setup_step7') !!}</li>
                        <li>{!! __('cipi.server_setup_step8') !!}</li>
                    </ul>
                </p>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteServerModal" tabindex="-1" role="dialog" aria-labelledby="deleteServerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteServerModalLabel">{{ __('cipi.delete_server') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('cipi.delete_server_confirmation') }} <b><span id="deleteservername"></span></b>?</p>
                <div class="space"></div>
                <label for="deleteserverip">{{ __('cipi.delete_server_confirmation_ip') }}: <i><span id="deleteserveriptocopy"></span></i></label>
                <div class="input-group">
                    <input class="form-control" type="text" id="deleteserverip" autocomplete="off" />
                </div>
                <input type="hidden" id="deleteserverid" value="" />
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="delete">{{ __('cipi.delete') }} <i class="fas fa-circle-notch fa-spin d-none" id="loadingdelete"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('css')

@endsection



@section('js')
<script>
    //Get DT Data
    getData('/api/servers');

    // Render Make
    function renderMake() {
        $('#dt').DataTable( {
            'processing': true,
            'data': JSON.parse(localStorage.getItem('dtdata')),
            'columns': [
                { data: 'name' },
                { data: 'ip' },
                { data: 'provider' },
                { data: 'location' },
                { data: {
                    'server_id': 'server_id',
                    'default': 'default',
                    'name': 'name',
                    'status': 'status',
                    'ip': 'ip'
                }}
            ],
            'columnDefs': [
                {
                    'targets': 0,
                    'className': 'd-none d-md-table-cell',
                },
                {
                    'targets': 1,
                    'className': 'text-center',
                },
                {
                    'targets': 2,
                    'className': 'text-center d-none d-lg-table-cell',
                },
                {
                    'targets': 3,
                    'className': 'text-center d-none d-xl-table-cell',
                },
                {
                    'targets': 4,
                    'className': 'text-center',
                    'render': function ( data, type, row, meta ) {
                        if(data['status'] == 0) {
                            if(data['default']) {
                                return '<span class="btn btn-sm btn-warning mr-3"><i class="fas fa-circle-notch fa-spin fa-fw"></i> <b class="d-none d-sm-inline">Wait...</b></span><span class="disabled btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></span>';
                            } else {
                                return '<button data-id="'+data['server_id']+'" data-ip="'+data['ip']+'" class="btinstall btn btn-sm btn-secondary mr-3"><i class="fas fa-terminal fa-fw"></i> <b class="d-none d-sm-inline">Install</b></button><button data-id="'+data['server_id']+'" data-name="'+data['name']+'" data-ip="'+data['ip']+'" class="btdelete btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></button>';
                            }
                        } else {
                            if(data['default']) {
                                return '<button data-id="'+data['server_id']+'" class="btmanage btn btn-sm btn-primary mr-3"><i class="fas fa-cog fa-fw"></i> <b class="d-none d-sm-inline">Manage</b></button><span class="disabled btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></span>';
                            } else {
                                return '<button data-id="'+data['server_id']+'" class="btmanage btn btn-sm btn-primary mr-3"><i class="fas fa-cog fa-fw"></i> <b class="d-none d-sm-inline">Manage</b></button><button data-id="'+data['server_id']+'" data-name="'+data['name']+'" data-ip="'+data['ip']+'" class="btdelete btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></button>';
                            }
                        }

                    }
                }
            ],
            'bLengthChange': false,
            'bAutoWidth': true,
            'responsive': true,
            'drawCallback': function(settings) {
                //Manage Server
                $(".btmanage").click(function() {
                    window.location.href = '/servers/'+$(this).attr('data-id');
                });
                //Delete Server
                $(".btdelete").click(function() {
                    serverDelete($(this).attr('data-id'),$(this).attr('data-ip'),$(this).attr('data-name'));
                });
                //Setup Server
                $(".btinstall").click(function() {
                    $('#installserverid').html($(this).attr('data-id'));
                    $('#installserverssh').html($(this).attr('data-ip'));
                    $('#installServerModal').modal();
                });
            }
        });
    }

    //Delete Server
    function serverDelete(server_id,ip,name) {
        validation = false;
        $('#deleteserverid').val(server_id);
        $('#deleteservername').html(name);
        $('#deleteserveriptocopy').html(ip);
        $('#deleteServerModal').modal();
        $('#deleteserverip').blur(function() {
            if($('#deleteserverip').val() == ip) {
                validation = true;
                $('#delete').removeClass('disabled');
            }
        });
        $('#deleteserverip').keyup(function() {
            if($('#deleteserverip').val() != ip) {
                validation = false;
                $('#delete').addClass('disabled');
            }
        });
        $('#delete').click(function() {
            if(validation) {
                $.ajax({
                    url: '/api/servers/'+$('#deleteserverid').val(),
                    type: 'DELETE',
                    contentType: 'application/json',
                    dataType: 'json',
                    beforeSend: function() {
                        validation = false;
                        $('#loadingdelete').removeClass('d-none');
                    },
                    complete: function(data) {
                        getData('/api/servers',false);
                        $('#deleteServerModal').modal('toggle');
                        $('#deleteservername').html('');
                        $('#deleteserverip').val('');
                        $('#deleteserverid').val('');
                        $('#deleteserveriptocopy').html('');
                        $('#loadingdelete').addClass('d-none');
                    },
                });
            }
        });
    }

    //Auto Update List
    setInterval(function() {
        $('#dt').DataTable().clear().destroy();
        getData('/api/servers',false);
    }, 45000);

    //Check IP conflict
    function ipConflict(ip) {
        conflict = 0;
        getDataNoUI('/api/servers',false);
        JSON.parse(localStorage.dtdata).forEach(server => {
            if(ip === server.ip) {
                conflict = conflict + 1;
            }
        });
        return conflict;
    }

    //New Server
    $('#newServer').click(function() {
        $('#newserverid').html('');
        $('#newserverssh').html('');
        $('#newserverform').removeClass('d-none');
        $('#newserverok').addClass('d-none');
        $('#loading').addClass('d-none');
        $('#newserverdialog').removeClass('modal-lg');
        $('#newServerModal').modal();
    });

    //New Server Validation
    $('#newservername').keyup(function() {
        $('#newservername').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });
    $('#newserverip').keyup(function() {
        $('#newserverip').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });

    //New Server Submit
    $('#submit').click(function() {
        validation = true;
        if(!$('#newservername').val() || $('#newservername').val().length < 3) {
            $('#newservername').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        if(!$('#newserverip').val() || !ipValidate($('#newserverip').val()) || ipConflict($('#newserverip').val()) > 0) {
            $('#newserverip').addClass('is-invalid');
            $('#submit').addClass('disabled');
            validation = false;
        }
        if(validation) {
            $.ajax({
                url: '/api/servers',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'name':     $('#newservername').val(),
                    'ip':       $('#newserverip').val(),
                    'provider': $('#newserverprovider').val(),
                    'location': $('#newserverlocation').val()
                }),
                beforeSend: function() {
                    $('#loading').removeClass('d-none');
                },
                success: function(data) {
                    $('#dt').DataTable().clear().destroy();
                    getData('/api/servers',false);
                    $('#loading').addClass('d-none');
                    $('#newserverdialog').addClass('modal-lg');
                    $('#newserverid').html(data.server_id);
                    $('#newserverssh').html(data.ip);
                    $('#newserverform').addClass('d-none');
                    $('#newserverok').removeClass('d-none');
                    $('#newservername').val('');
                    $('#newserverip').val('');
                    $('#newserverprovider').val('');
                    $('#newserverlocation').val('');
                },
            });
        }
    });
</script>
@endsection
