// Get Server info
$('#mainloading').removeClass('d-none');

// Site Init
function siteInit() {
    $.ajax({
        url: '/api/sites/{{ $site_id }}',
        type: 'GET',
        success: function(data) {
            $('#mainloading').addClass('d-none');
            $('#siteip').html(data.server_ip);
            $('#sitealiases').html(data.aliases);
            $('#sitephp').html(data.php);
            $('#sitebasepathinfo').html(data.basepath);
            $('#siteuserinfo').html(data.username);
            $('#maintitle').html(data.domain);
            $('#sitedomain').val(data.domain);
            $('#sitebasepath').val(data.basepath);
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