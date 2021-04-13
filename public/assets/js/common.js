//Init Datatable Data
localStorage.dtdata = '';

//Vars in Page
$('#username').html(localStorage.username);
$('#panelversion').html($('meta[name="cipi-version"]').attr('content'));

//Success notification
function success(text) {
    $('#successtext').empty();
    $('#successtext').html(text);
    $('#success').removeClass('d-none');
}

//Fail notification
function fail(text) {
    $('#failtext').empty();
    $('#failtext').html(text);
    $('#fail').removeClass('d-none');
}

//Success notification hide
$('#successx').click(function() {
    $('#success').addClass('d-none');
    $('#successtext').empty();
});

//Fail notification hide
$('#failx').click(function() {
    $('#fail').addClass('d-none');
    $('#failtext').empty();
});

//Tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

//IP Validation
function ipValidate(ip) {
    return (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip))
}

//jQuery AJAX Authorization Header Setup & Default Error
$.ajaxSetup({
    cache: false,
    headers: {
        'Authorization': 'Bearer '+localStorage.access_token,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    },
    error: function(error) {
        if(error.status == 401) {
            jwtrefresh().then(data => {
                this.headers = {'Authorization': 'Bearer '+localStorage.access_token };
                $.ajax(this);
            }).catch(error => {
                $('#errorModal').modal();
            });
        }
        if(error.status == 404) {
            window.location.replace('/error-file-not-found');
        }
        if(error.status == 500) {
            $('#errorModal').modal();
        }
        if(error.status == 503) {
            $('#serverping').empty();
            $('#serverping').html('<i class="fas fa-times text-danger"></i>');
        }
    }
});

//JWT Token Refresh
function jwtrefresh() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/auth',
            type: 'GET',
            data: {
                username: localStorage.username,
                refresh_token: localStorage.refresh_token
            },
            success: function(data) {
                localStorage.access_token = data.access_token;
                localStorage.refresh_token = data.refresh_token;
                localStorage.username = data.username;
                resolve(data);
            },
            error: function(error) {
                localStorage.clear();
                window.location.replace('/login');
                reject(error);
            }
        });
    });
}

//Get Data for DataTable
function getData(url,loading=true) {
    if(loading) {
        $('#mainloading').removeClass('d-none');
    }
    $.ajax({
        type: 'GET',
        url: url,
        success: function(data) {
            localStorage.dtdata = '';
            localStorage.dtdata = JSON.stringify(data);
            dtRender();
            if(loading) {
                setTimeout(function() {
                    $('#mainloading').addClass('d-none');
                }, 250);
            }
        }
    });
}

//Get Data for Other
function getDataNoDT(url) {
    $.ajax({
        type: 'GET',
        url: url,
        success: function(data) {
            localStorage.otherdata = '';
            localStorage.otherdata = JSON.stringify(data);
        }
    });
}

//Get Data for DataTable
function getDataNoUI(url) {
    $.ajax({
        type: 'GET',
        url: url,
        success: function(data) {
            localStorage.dtdata = '';
            localStorage.dtdata = JSON.stringify(data);
        }
    });
}

//Logout
$('#logout').click(function(e) {
    e.preventDefault();
    $.ajax({
        url: '/auth',
        type: 'DELETE',
        headers: {
            'x-csrf-token': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        data: {
            'username': localStorage.username,
            'refresh_token': localStorage.refresh_token
        },
        success: function(data) {
            localStorage.clear();
            window.location.replace('/login');
        }
    });
});