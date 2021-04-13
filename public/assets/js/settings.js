//Username in Card
$('#currentuser').html(localStorage.username);

//Panel URL info
$.ajax({
    url: '/api/servers/panel',
    type: 'GET',
    success: function(data) {
        $('#panelurl').val(data.domain);
    },
});

//Panel URL change
$('#panelurlsubmit').click(function() {
    $.ajax({
        url: '/api/servers/panel/domain',
        type: 'PATCH',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({
            'domain': $('#panelurl').val(),
        }),
        beforeSend: function() {
            $('#panelurlsubmit').html('<i class="fas fa-circle-notch fa-spin"></i>');
        },
        complete: function(data) {
            $('#panelurlsubmit').empty();
            $('#panelurlsubmit').html('<i class="fas fas fa-edit"></i>');
        },
    });
});

//Panel URL change
$('#panelurlssl').click(function() {
    $.ajax({
        url: '/api/servers/panel/ssl',
        type: 'POST',
        beforeSend: function() {
            $('#panelurlssl').html('<i class="fas fa-circle-notch fa-spin"></i>');
        },
        complete: function(data) {
            $('#panelurlssl').empty();
            $('#panelurlssl').html('<i class="fas fas fa-lock"></i>');
        },
    });
});

//Username Patch
$('#newuser').keyup(function() {
    $('#newuser').removeClass('is-invalid');
});
$('#changeuser').click(function() {
    userval = $('#newuser').val();
    if(!userval || userval.length < 6 || userval == localStorage.username) {
        $('#newuser').addClass('is-invalid');
    } else {
        $('#authorizeModal').modal();
        patchcall = 'changeuser';
    }
});

//Password Patch
$('#newpass').keyup(function() {
    $('#newpass').removeClass('is-invalid');
});
$('#changepass').click(function() {
    passval = $('#newpass').val();
    if(!passval || passval.length < 8) {
        $('#newpass').addClass('is-invalid');
    } else {
        $('#authorizeModal').modal();
        patchcall = 'changepass';
    }
});

//API Key Renew
$('#newapikey').click(function() {
    $('#authorizeModal').modal();
    patchcall = 'newapikey';
});

//Submit
$('#submit').click(function() {
    oldpassval = $('#currentpass').val();
    if(!oldpassval || oldpassval.length < 8) {
        $('#newpass').addClass('is-invalid');
    } else {
        if(patchcall == 'changeuser') {
            calldata = {
                username: localStorage.username,
                password: $('#currentpass').val(),
                newusername: $('#newuser').val()
            }
        }
        if(patchcall == 'changepass') {
            calldata = {
                username: localStorage.username,
                password: $('#currentpass').val(),
                newpassword: $('#newpass').val()
            }
        }
        if(patchcall == 'newapikey') {
            calldata = {
                username: localStorage.username,
                password: $('#currentpass').val(),
                apikey: true
            }
        }
        $.ajax({
            url: '/auth',
            type: 'PATCH',
            headers: {
                'x-csrf-token': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            data: calldata,
            beforeSend: function() {
                $('#loading').removeClass('d-none');
            },
            complete: function() {
                $('#loading').addClass('d-none');
            },
            success: function(data) {
                $('#newuser').val('');
                $('#newpass').val('');
                $('#currentpass').val('');
                localStorage.access_token=data.access_token;
                localStorage.refresh_token=data.refresh_token;
                localStorage.username=data.username;
                if(patchcall == 'changeuser') {
                    success('Username has been updated');
                    $('#currentuser').html(localStorage.username);
                    $('#username').html(localStorage.username);
                }
                if(patchcall == 'changepass') {
                    success('Password has been updated');
                }
                if(patchcall == 'newapikey') {
                    success('New API Key:<br><b>'+data.apikey+'</b>');
                }
                $('#authorizeModal').modal("hide");
                $(window).scrollTop(0);
            },
            error: function(error) {
                if(error.status == 401) {
                    $('#currentpass').addClass('is-invalid');
                } else {
                    $('#newuser').val('');
                    $('#newpass').val('');
                    $('#currentpass').val('');
                    fail('Ops! Something went wrong... Try again!');
                    $('#authorizeModal').modal("hide");
                }
            }
        });
    }
});

//Old password validation reset
$('#currentpass').keyup(function() {
    $('#currentpass').removeClass('is-invalid');
});