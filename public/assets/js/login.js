//Clear current auth
localStorage.clear();

//Validation check
function loginValidate() {
    validation = true;
    if(!$('#username').val()) {
        $('#username').addClass('is-invalid');
        validation = false;
    }
    if(!$('#password').val()) {
        $('#password').addClass('is-invalid');
        validation = false;
    }
    return validation;
}

//Login
function loginSubmit() {
    if(loginValidate() == true) {
        $.ajax({
            url: '/auth',
            type: 'POST',
            headers: {
                'x-csrf-token': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                'username': $('#username').val(),
                'password': $('#password').val()
            },
            beforeSend: function() {
                $('#loading').removeClass('d-none');
                $('#username').removeClass('is-invalid');
                $('#password').removeClass('is-invalid');
            },
            complete: function() {
                $('#username').blur();
                $('#password').blur();
                $('#loading').addClass('d-none');
            },
            success: function(data) {
                localStorage.access_token=data.access_token;
                localStorage.refresh_token=data.refresh_token;
                localStorage.username=data.username;
                window.location.replace('/dashboard');
            },
            error: function() {
                $('#username').addClass('is-invalid');
                $('#password').addClass('is-invalid');
            }
        });
    }
}

//Keyup Validation
$('#username').keyup(function() {
    $('#username').removeClass('is-invalid');
});
$('#password').keyup(function() {
    $('#password').removeClass('is-invalid');
});
$('#username').blur(function() {
    loginValidate();
});
$('#password').blur(function() {
    loginValidate();
});

//Submit via Mouse Click
$('#login').on('click', function() {
    loginSubmit();
});

//Submit via Enter Key
$(document).keypress(function(e) {
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if(keycode == '13'){
        loginSubmit(); 
    }
});
