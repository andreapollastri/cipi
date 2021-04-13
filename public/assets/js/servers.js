//Get DT Data 
getData('/api/servers');

//Datatable
function dtRender() {
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
                success: function(data) {
                    $('#dt').DataTable().clear().destroy();
                    getData('/api/servers',false);
                    $('#deleteServerModal').modal('toggle');
                    $('#deleteservername').html('');
                    $('#deleteserverip').val('');
                    $('#deleteserverid').val('');
                    $('#deleteserveriptocopy').html('');
                    $('#loadingdelete').addClass('d-none');
                },
                complete: function(data) {
                    $('#dt').DataTable().clear().destroy();
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