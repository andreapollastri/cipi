@extends('template')


@section('title')
Sites
@endsection



@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card mb-4">
            <div class="card-header text-right">
                <button class="btn btn-sm btn-secondary" id="newSite">
                    <i class="fas fa-plus mr-1"></i><b>New Site</b>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dt" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">Domain</th>
                                <th class="text-center text-center d-none d-md-table-cell">Aliases</th>
                                <th class="text-center d-none d-lg-table-cell">Server</th>
                                <th class="text-center d-none d-xl-table-cell">IP</th>
                                <th class="text-center">Actions</th>
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
<div class="modal fade" id="newSiteModal" tabindex="-1" role="dialog" aria-labelledby="newSiteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="newsitedialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSiteModalLabel">Add a new site</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="newsiteform">
                    <label for="newsitedomain">Site domain</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newsitedomain" placeholder="e.g. domain.ltd" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <label for="newsiteserver">Server</label>
                    <div class="input-group">
                        <select class="form-control" id="newsiteserver"></select>
                    </div>
                    <div class="space"></div>
                    <label for="newsiteprovider">PHP Version</label>
                    <div class="input-group">
                        <select class="form-control" id="newsitephp">
                            <option value="8.0" selected>8.0</option>
                            <option value="7.4">7.4</option>
                            <option value="7.3">7.3</option>
                        </select>
                    </div>
                    <div class="space"></div>
                    <label for="newsitebasepath">Basepath</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="newsitebasepath" placeholder="e.g. public" autocomplete="off" />
                    </div>
                    <div class="space"></div>
                    <div class="text-center">
                        <button class="btn btn-primary" type="button" id="submit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="loading"></i></button>
                    </div>
                </div>
                <div id="newsiteok" class="d-none container">
                    <div class="row">
                        <div class="col-xs-12">
                            <p><b>Your site is ready!</b></b>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <p>Domain:<br><b><span id="newsitedomainok"></b></b></p>
                            <p>Server IP:<br><b><span id="newsiteip"></b></b></p>
                            <p>SSH Username:<br><b><span id="newsiteusername"></b></p>
                            <p>SSH Password:<br><b><span id="newsitepassword"></b></p>
                            <p>MySQL database:<br><b><span id="newsitedbname"></b></p>
                            <p>MySQL username:<br><b><span id="newsitedbusername"></b></p>
                            <p>MySQL password:<br><b><span id="newsitedbpassword"></b></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <p>Document root:<br><b>/home/<span id="newsitebasepathuser"></span>/web/<span id="newsitebasepath"></b></p>
                        </div>
                    </div>
                    <div class="space"></div>
                    <div class="row">
                        <div class="col-xl-12 text-center">
                            <a href="" target="_blank" id="newsitepdf">
                                <button class="btn btn-success" type="button"><i class="fas fa-file-pdf"></i> Download (3 minutes link)</button>
                            </a>
                        </div>
                    </div>
                    <div class="space"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteSiteModal" tabindex="-1" role="dialog" aria-labelledby="deleteSiteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSiteModalLabel">Delete site</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to delete site <b><span id="deletesitename"></span></b> and its database and aliases?</p>
                <div class="space"></div>
                <input type="hidden" id="deletesiteid" value="" />
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="delete">Delete <i class="fas fa-circle-notch fa-spin d-none" id="loadingdelete"></i></button>
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
    getData('/api/sites');

    //Datatable
    function dtRender() {
        $('#dt').DataTable( {
            'processing': true,
            'data': JSON.parse(localStorage.getItem('dtdata')),
            'columns': [
                { data: 'domain' },
                { data: 'aliases' },
                { data: 'server_name' },
                { data: 'server_ip' },
                { data: {
                    'site_id': 'site_id',
                    'domain': 'domain',
                }}
            ],
            'columnDefs': [
                {
                    'targets': 1,
                    'className': 'd-none d-md-table-cell text-center',
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
                        return '<button data-id="'+data['site_id']+'" class="btmanage btn btn-sm btn-primary mr-3"><i class="fas fa-cog fa-fw"></i> <b class="d-none d-sm-inline">Manage</b></button><button data-id="'+data['site_id']+'" data-name="'+data['domain']+'" class="btdelete btn btn-sm btn-danger"><i class="fas fa-times fa-fw"></i> <b class="d-none d-sm-inline">Delete</b></button>';
                    }
                }
            ],
            'bLengthChange': false,
            'bAutoWidth': true,
            'responsive': true,
            'drawCallback': function(settings) {
                //Manage Site
                $(".btmanage").click(function() {
                    window.location.href = '/sites/'+$(this).attr('data-id');
                });
                //Delete Site
                $(".btdelete").click(function() {
                    siteDelete($(this).attr('data-id'),$(this).attr('data-domain'));
                });
            }
        });
    }

    //Delete Site
    function siteDelete(site_id,domain) {
        $('#deletesiteid').val(site_id);
        $('#deletesitedomain').html(domain);
        $('#deleteSiteModal').modal();
        $('#delete').click(function() {
            $.ajax({
                url: '/api/sites/'+$('#deletesiteid').val(),
                type: 'DELETE',
                contentType: 'application/json',
                dataType: 'json',
                beforeSend: function() {
                    $('#loadingdelete').removeClass('d-none');
                },
                complete: function(data) {
                    setTimeout(function() {
                        $('#dt').DataTable().clear().destroy();
                    }, 4500);
                    setTimeout(function() {
                        getData('/api/sites',false);
                    }, 6000);
                    setTimeout(function() {
                        $('#deleteSiteModal').modal('toggle');
                        $('#deletesitedomain').html('');
                        $('#deletesiteid').val('');
                        $('#loadingdelete').addClass('d-none');
                    }, 6500);
                },
            });
        });
    }

    //Auto Update List
    setInterval(function() {
        $('#dt').DataTable().clear().destroy();
        getData('/api/sites',false);
    }, 45000);

    //Get server domains
    $('#newsiteserver').change(function() {
        getDataNoDT('/api/servers/'+$('#newsiteserver').val()+'/domains');
    });

    //Check Domain Conflict
    function domainConflict(domain) {
        conflict = 0;
        JSON.parse(localStorage.otherdata).forEach(item => {
            if(item == domain) {
                conflict = conflict + 1;
            }
        });
        return conflict;
    }

    //Server list
    function getServers() {
        $('#newsiteserver').empty();
        $.ajax({
            type: 'GET',
            url: '/api/servers',
            success: function(data) {
                data.forEach(server => {
                    if(server.status) {
                        if(server.default) {
                            $('#newsiteserver').append('<option value="'+server.server_id+'" selected>'+server.name+' ('+server.ip+')</option>');
                            getDataNoDT('/api/servers/'+server.server_id+'/domains');
                        } else {
                            $('#newsiteserver').append('<option value="'+server.server_id+'">'+server.name+' ('+server.ip+')</option>');
                        }
                    }
                });
            }
        });
    }
    getServers();

    //New Site
    $('#newSite').click(function() {
        $('#loading').addClass('d-none');
        $('#newsiteform').removeClass('d-none');
        $('#newsiteok').addClass('d-none');
        $('#newsiteip').html();
        $('#newsiteusername').html();
        $('#newsitepassword').html();
        $('#newsitedbname').html();
        $('#newsitedbusername').html();
        $('#newsitedbpassword').html();
        $('#newsitebasepathuser').html();
        $('#newsitebasepath').html();
        $('#newsitedomainok').html();
        $('#newsitepdf').attr('href','#');
        $('#newSiteModal').modal();
    });

    //New Site Validation
    $('#newsitedomain').keyup(function() {
        $('#newsitedomain').removeClass('is-invalid');
        $('#submit').removeClass('disabled');
    });

    //New Site Submit
    $('#submit').click(function() {
        validation = true;
        // if(!$('#newsitedomain').val() || $('#newsitedomain').val().length < 5 || domainConflict($('#newsitedomain').val()) > 0) {
        //     $('#newsitedomain').addClass('is-invalid');
        //     $('#submit').addClass('disabled');
        //     validation = false;
        // }
        if(validation) {
            $.ajax({
                url: '/api/sites',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'domain':   $('#newsitedomain').val(),
                    'server_id':$('#newsiteserver').val(),
                    'php':      $('#newsitephp').val(),
                    'basepath': $('#newsitebasepath').val()
                }),
                beforeSend: function() {
                    $('#loading').removeClass('d-none');
                },
                success: function(data) {
                    $('#dt').DataTable().clear().destroy();
                    getData('/api/sites',false);
                    $('#loading').addClass('d-none');
                    $('#newsiteip').html(data.server_ip);
                    $('#newsiteusername').html(data.username);
                    $('#newsitepassword').html(data.password);
                    $('#newsitedbname').html(data.database);
                    $('#newsitedbusername').html(data.database_username);
                    $('#newsitedbpassword').html(data.database_password);
                    $('#newsitebasepathuser').html(data.username);
                    $('#newsitebasepath').html(data.basepath);
                    $('#newsitedomainok').html(data.domain);
                    $('#newsitepdf').attr('href',data.pdf);
                    $('#newsiteform').addClass('d-none');
                    $('#newsiteok').removeClass('d-none');
                    $('#newsitedomain').val('');
                    $('#newsitephp').val('8.0');
                    $('#newsitebasepath').val('');
                    getServers();
                },
            });
        }
    });
</script>
@endsection