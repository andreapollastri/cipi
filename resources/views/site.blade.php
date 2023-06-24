@extends('template')


@section('title')
Manage Site
@endsection



@section('content')
<ol class="breadcrumb mb-4">
    <li class="ml-1 breadcrumb-item active">IP:<b><span class="ml-1" id="siteip"></span></b></li>
    <li class="ml-1 breadcrumb-item active">ALIASES:<b><span class="ml-1" id="sitealiases"></span></b></li>
    <li id="breadcrumbPHP" class="ml-1 breadcrumb-item active">PHP:<b><span class="ml-1" id="sitephp"></span></b></li>
    <li class="ml-1 breadcrumb-item active">DIR:<b><span class="ml-1">/home/</span><span id="siteuserinfo"></span>/web/<span id="sitebasepathinfo"></span></b></li>
    <li class="ml-1 breadcrumb-item active">LANGUAGE:<b><span class="ml-1" id="sitelang"></span></b></li>
</ol>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle fs-fw mr-1"></i>
                Basic information
            </div>
            <div class="card-body">
                <p>Domain:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. domain.ltd" id="sitedomain" autocomplete="off" />
                </div>
                <div class="space"></div>
                <p>Basepath:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. public" id="sitebasepath" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="updateSite">Update <i class="fas fa-circle-notch fa-spin d-none" id="updateSiteloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-globe fs-fw mr-1"></i>
                Manage aliases
            </div>
            <div class="card-body">
                <p>Add alias:</p>
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="e.g. www.domain.ltd" id="siteaddalias" autocomplete="off" />
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="siteaddaliassubmit"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="space"></div>
                <div style="min-height:135px">
                    <p>Aliases:</p>
                    <div id="sitealiaseslist"></div>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-lock fs-fw mr-1"></i>
                SSLs and Security
            </div>
            <div class="card-body">
                <p>Require and generate free Let's Encrypt certificate for site domain and aliases:</p>
                <button class="btn btn-success btn" type="button" id="sitessl">Generate SSLs <i class="fas fa-circle-notch fa-spin d-none" id="sitesslloading"></i></button>
                <div class="space"></div>
                <div class="space"></div>
                <p>Passwords reset:</p>
                <button class="btn btn-warning btn mr-3" type="button" id="sitesshreset">SSH</button>
                <button class="btn btn-warning btn mr-3" type="button" id="sitemysqlreset">MySql</button>
                <div class="space" style="min-height:38px"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-rocket fs-fw mr-1"></i>
                Application installer
            </div>
            <div class="card-body text-center">
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <h5>Coming soon...</h5>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
                Github repository
            </div>
            <div class="card-body">
                <p>Set up a Github repository</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="sitesetrepo">Repo configuration</button>
                    <div class="space"></div>
                </div>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="editdeploy">Edit deploy scripts</button>
                    <div class="space"></div>
                </div>
                <p>
                    To run deploy:
                    <ul style="font-size:14px;">
                        <li>ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span></li>
                        <li>sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-tools fs-fw mr-1"></i>
                Tools
            </div>
            <div class="card-body">
                <div id="sitephpverField">
                    <p>PHP-FPM version:</p>
                    <div class="input-group">
                        <select class="form-control" id="sitephpver">
                            <option value="8.0" id="php80">8.0</option>
                            <option value="7.4" id="php74">7.4</option>
                            <option value="7.3" id="php73">7.3</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="sitephpversubmit"><i class="fas fa-edit"></i></button>
                        </div>
                    </div>
                    <div class="space"></div>
                </div>

                <p>Supervisor script:</p>
                <div class="input-group">
                    <input class="form-control" type="text" id="sitesupervisor" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="sitesupervisorupdate">Update <i class="fas fa-circle-notch fa-spin d-none" id="sitesupervisorupdateloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-rocket fs-fw mr-1"></i>
                File Manager
            </div>
            <div class="card-body text-center">
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <form action="{{route('files.index')}}" method="get" >
                    <input type="hidden" name="site-uuid" id="siteuuid">
                    <input type="submit" value="Open Manager" class="btn btn-success">
                </form>
                {{-- <form class="btn btn-primary" href="">File Manager</a> --}}
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
                <div class="space"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
                PhpMyAdmin
            </div>
            <div class="card-body">
                <p>Import your database</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="sitesetrepo"> <a href="{{ route('autopma',$site_id) }}" class="btn btn-warning" style="min-width:200px" target="_blank">phpMyAdmin</a></button>
                    <div class="space"></div>
                </div>
                {{-- <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="editdeploy">Edit deploy scripts</button>
                    <div class="space"></div>
                </div> --}}
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
                MYSQL
            </div>
            <div class="card-body">
                <p>Set up your database</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="sitesetrepo"> <a href="{{ url('/data') }}" class="btn btn-warning" style="min-width:200px">DATABASE</a></button>
                    <div class="space"></div>
                </div>
                {{-- <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="editdeploy">Edit deploy scripts</button>
                    <div class="space"></div>
                </div> --}}
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fab fa-github fs-fw mr-1"></i>
                Nodejs Manager
            </div>
            <div class="card-body">
                <p>Manage your project</p>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="startNodejsButton">Setup App</button>
                    <div class="space"></div>
                </div>
                <div class="text-center">
                    <button class="btn btn-warning" type="button" style="min-width:200px" id="editdeploy">Stop App</button>
                    <div class="space"></div>
                </div>
                <p>
                    To run deploy:
                <ul style="font-size:14px;">
                    <li>ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span></li>
                    <li>sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh</li>
                </ul>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection



@section('extra')
<input type="hidden" id="currentdomain">
<input type="hidden" id="server_id">
<div class="modal fade" id="repositoryModal" tabindex="-1" role="dialog" aria-labelledby="repositoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="repositorydialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repositoryModalLabel">Github repository</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="repositoryproject">Project</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositoryproject" placeholder="e.g. johndoe/helloworld" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="repositorybranch">Branch</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositorybranch" placeholder="e.g. develop" autocomplete="off" />
                </div>
                <div class="space"></div>
                <label for="deploykey">Deploy Key (<a href="#" id="copykey">Copy</a> and add it <a href="https://github.com/settings/ssh/new" target="blank">here</a>)</label>
                <div class="input-group">
                    <textarea id="deploykey" readonly style="width:100%;height:150px;font-size:10px;"></textarea>
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="repositorysubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="repositoryloading"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deployModal" tabindex="-1" role="dialog" aria-labelledby="deployModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deployModalLabel">Site deploy scripts</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Edit site deploy scripts:</p>
                <div id="deploy" style="height:250px;width:100%;"></div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="deploysubmit">Save <i class="fas fa-circle-notch fa-spin d-none" id="deployloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sshresetModal" tabindex="-1" role="dialog" aria-labelledby="sshresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sshresetModalLabel">Request password reset</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to reset site SSH password?</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="sshresetsubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="sshresetloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mysqlresetModal" tabindex="-1" role="dialog" aria-labelledby="mysqlresetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mysqlresetModalLabel">Request password reset</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to reset site MySql password?</p>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-danger" type="button" id="mysqlresetsubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="mysqlresetloading"></i></button>
                </div>
                <div class="space"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="startNodejsModal" tabindex="-1" role="dialog" aria-labelledby="startNodejsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" id="repositorydialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repositoryModalLabel">Setup Your Nodejs</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label for="repositoryproject">Path</label>
                <div class="input-group">
                    <input class="form-control" type="text" id="repositoryproject" placeholder="e.g. ./bin/www" autocomplete="off" />
                </div>
                <div class="space"></div>
                <div class="text-center">
                    <button class="btn btn-primary" type="button" id="repositorysubmit">Confirm <i class="fas fa-circle-notch fa-spin d-none" id="repositoryloading"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('css')

@endsection



@section('js')
<script>
    // Get Server info
    $('#mainloading').removeClass('d-none');

    // Site Init
    function siteInit() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'GET',
            success: function(data) {
                console.log(data);
                $('#mainloading').addClass('d-none');
                $('#siteip').html(data.server_ip);
                $('#sitealiases').html(data.aliases);
                $('#sitephp').html(data.php);
                $('#sitebasepathinfo').html(data.basepath);
                $('#siteuserinfo').html(data.username);
                $('#maintitle').html(data.domain);
                $('#sitedomain').val(data.domain);
                $('#sitebasepath').val(data.basepath);
                $('#sitelang').html(data.language);
                if(data.language != "PHP"){
                    $('#sitephpverField').addClass('d-none');
                    $('#breadcrumbPHP').addClass('d-none');
                }
                //added
                console.log(data.rootpath);
                $('#siteuuid').val(data.rootpath);
                //
                $('#currentdomain').val(data.domain);
                $('#server_id').val(data.server_id);
                $('#sitesupervisor').val(data.supervisor);
                $('#deploykey').html(data.deploy_key)
                $('#repodeployinfouser1').html(data.username);
                $('#repodeployinfouser2').html(data.username);
                $('#repodeployinfoip').html(data.server_ip);
                $('#repositoryproject').val(data.repository);
                $('#repositorybranch').val(data.branch);
                deploy.session.setValue(data.deploy);
                getDataNoDT('/api/servers/'+data.server_id+'/domains');
                switch (data.php) {
                    case '8.0':
                        $('#php80').attr("selected","selected");
                        break;
                    case '7.4':
                        $('#php74').attr("selected","selected");
                        break;
                    case '7.3':
                        $('#php73').attr("selected","selected");
                        break;
                    default:
                        break;
                }
            },
        });
        $.ajax({
            url: '/api/sites/{{ $site_id }}/aliases',
            type: 'GET',
            success: function(data) {
                $('#sitealiaseslist').empty();
                jQuery(data).each(function(i, item){
                    $('#sitealiaseslist').append('<span class="badge badge-info mr-2 ml-2">'+item.domain+'<i data-id="'+item.alias_id+'" style="cursor:pointer" class="sitealiasdel fas fa-times fs-fw ml-2"></i></span>');
                });
            },
        });
    }
    $(document).ajaxSuccess(function(){
        aliasesDelete();
    });

    // Init variables
    siteInit();

    // Password reset
    $('#sitesshreset').click(function() {
        $('#sshresetModal').modal();
    });
    $('#sshresetsubmit').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}/reset/ssh',
            type: 'POST',
            beforeSend: function() {
                $('#sshresetloading').removeClass('d-none');
            },
            success: function(data) {
                success('Your SSH password has been reset:<br><b>'+data.password+'</b><br><a href="'+data.pdf+'" target="_blank" style="color:#ffffff">Download PDF (3 minutes link)</a>');
                $('#sshresetloading').addClass('d-none');
                $('#sshresetModal').modal('toggle');
                $(window).scrollTop(0);
            }
        });
    });

    // DB Password reset
    $('#sitemysqlreset').click(function() {
        $('#mysqlresetModal').modal();
    });
    $('#mysqlresetsubmit').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}/reset/db',
            type: 'POST',
            beforeSend: function() {
                $('#mysqlresetloading').removeClass('d-none');
            },
            success: function(data) {
                success('Your Mysql password has been reset:<br><b>'+data.password+'</b><br><a href="'+data.pdf+'" target="_blank" style="color:#ffffff">Download PDF (3 minutes link)</a>');
                $('#mysqlresetloading').addClass('d-none');
                $('#mysqlresetModal').modal('toggle');
                $(window).scrollTop(0);
            }
        });
    });

    // SSLs Require
    $('#sitessl').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}/ssl',
            type: 'POST',
            beforeSend: function() {
                $('#sitesslloading').removeClass('d-none');
            },
            success: function(data) {
                $('#sitesslloading').addClass('d-none');
            }
        });
    });


    // Repository
    $('#sitesetrepo').click(function() {
        $('#repositoryModal').modal();
    });

    // Repository
    $('#startNodejsButton').click(function() {
        $('#startNodejsModal').modal();
    });

    // Repository Submit
    $('#repositorysubmit').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'repository': $('#repositoryproject').val(),
                'branch': $('#repositorybranch').val(),
            }),
            beforeSend: function() {
                $('#repositoryloading').removeClass('d-none');
            },
            success: function(data) {
                $('#repositoryloading').addClass('d-none');
                $('#repositoryModal').modal('toggle');
                siteInit();
            },
        });
    });

    //Deploy Key Copy
    $("#copykey").click(function(){
        $("#deploykey").select();
        document.execCommand('copy');
    });

    // Deploy editor
    var deploy = ace.edit("deploy");
    deploy.setTheme("ace/theme/monokai");
    deploy.session.setMode("ace/mode/sh");

    // Deploy Edit
    $('#editdeploy').click(function() {
        $('#deployModal').modal();
    });

    // Deploy Submit
    $('#deploysubmit').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'deploy': deploy.getSession().getValue(),
            }),
            beforeSend: function() {
                $('#deployloading').removeClass('d-none');
            },
            success: function(data) {
                $('#deployloading').addClass('d-none');
                $('#deployModal').modal('toggle');
                siteInit();
            },
        });
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


    // Site Aliases
    $('#siteaddalias').keyup(function() {
        $('#siteaddalias').removeClass('is-invalid');
    });
    $('#siteaddaliassubmit').click(function() {
        if(domainConflict($('#siteaddalias').val()) < 1 && $('#siteaddalias').val() != '') {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/aliases',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    'domain': $('#siteaddalias').val(),
                }),
                beforeSend: function() {
                    $('#siteaddaliassubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
                },
                success: function(data) {
                    $('#siteaddalias').val('');
                    $('#siteaddaliassubmit').empty();
                    $('#siteaddaliassubmit').html('<i class="fas fas fa-edit"></i>');
                    siteInit();
                },
            });
        } else {
            $('#siteaddalias').addClass('is-invalid');
        }
    });

    //Delete Aliases
    function aliasesDelete() {
        $(".sitealiasdel").on("click", function() {
            $.ajax({
                url: '/api/sites/{{ $site_id }}/aliases/'+$(this).attr('data-id'),
                type: 'DELETE',
                success: function(data) {
                    $('#mainloading').removeClass('d-none');
                },
                complete: function() {
                    setTimeout(() => {
                        siteInit();
                    }, 5000);
                }
            });
        });
    }

    // Change PHP
    $('#sitephpversubmit').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'php': $('#sitephpver').val(),
            }),
            beforeSend: function() {
                $('#sitephpversubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
            },
            success: function(data) {
                $('#sitephpversubmit').empty();
                $('#sitephpversubmit').html('<i class="fas fas fa-edit"></i>');
                siteInit();
            },
        });
    });

    // Supervisor
    $('#sitesupervisorupdate').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'supervisor': $('#sitesupervisor').val(),
            }),
            beforeSend: function() {
                $('#sitesupervisorupdateloading').removeClass('d-none');
            },
            success: function(data) {
                $('#sitesupervisorupdateloading').addClass('d-none');
                siteInit();
            },
        });
    });

    // Basic info
    $('#updateSite').click(function() {
        $.ajax({
            url: '/api/sites/{{ $site_id }}',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({
                'domain': $('#sitedomain').val(),
                'basepath': $('#sitebasepath').val(),
            }),
            beforeSend: function() {
                $('#updateSiteloadingloading').removeClass('d-none');
            },
            success: function(data) {
                $('#updateSiteloadingloading').addClass('d-none');
                siteInit();
            },
        });
    });


</script>
@endsection
