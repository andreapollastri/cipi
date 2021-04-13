<!DOCTYPE html>
<script>if(localStorage.getItem('username') === null){window.location.replace('/login');} //Session Check</script>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    
    <head>
    <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>{{ config('cipi.name') }} | @yield('title')</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="cipi-version" content="{{ Storage::get('cipi/version.md') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js"></script>
        <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/responsive/2.2.7/css/responsive.bootstrap4.min.css" rel="stylesheet" />
        <link rel="icon" type="image/png" href="/favicon.png" />
        <link href="/assets/css/app.css" rel="stylesheet" />
        <style>
            .space {
                min-height: 20px;
            }
            #mainloading {
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                position: fixed;
                display: block;
                opacity: 0.8;
                background-color: #000;
                z-index: 99;
                text-align: center;
                color: #fff;
                font-family: Arial, Helvetica, sans-serif;
            }
            #mainloadingicon {
                margin: 0 auto;
                margin-top: 100px;    
                z-index: 100000;
            }
        </style>
        @yield('css')
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand" href="/dashboard"><i class="fab fa-fw fa-linux"></i> {{ config('cipi.name') }}</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0  d-lg-none" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">MENU</div>
                            <a class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}" href="/dashboard">
                                <div class="sb-nav-link-icon"><i class="fas fa-fw fa-th-large"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link {{ request()->is('servers*') ? 'active' : '' }}" href="/servers">
                                <div class="sb-nav-link-icon"><i class="fas fa-fw fa-server"></i></div>
                                Servers
                            </a>
                            <a class="nav-link {{ request()->is('sites*') ? 'active' : '' }}" href="/sites">
                                <div class="sb-nav-link-icon"><i class="fas fa-fw fa-rocket"></i></div>
                                Sites
                            </a>
                            <a class="nav-link {{ request()->is('servers*') ? 'settings' : '' }}" href="/settings">
                                <div class="sb-nav-link-icon"><i class="fas fa-fw fa-cog"></i></div>
                                Settings
                            </a>
                            <a class="nav-link" href="#" id="logout">
                                <div class="sb-nav-link-icon"><i class="fas fa-fw fa-sign-out-alt"></i></div>
                                Logout
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Panel version:</div>
                        <span id="panelversion"></span>
                        <div class="space"></div>
                        <div class="small">Logged in as:</div>
                        <span id="username"></span>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mt-4">@yield('title') <span id="maintitle"></span></h1>
                        <div class="space"></div>
                        <div id="success" class="d-none">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card bg-primary text-white mb-12">
                                        <div class="card-body">
                                            <b><i class="fas fa-check"></i></b> <span id="successtext"></span>
                                            <button type="button" class="close" id="successx">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="space"></div>
                        </div>
                        <div id="fail" class="d-none">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card bg-secondary text-white mb-12">
                                        <div class="card-body">
                                            <b><i class="fas fa-times"></i></b> <span id="failtext"></span>
                                            <button type="button" class="close" id="failx">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="space"></div>
                        </div>
                        @yield('content')
                    </div>
                </main>
            </div>
            <div id="mainloading" class="d-none">
                <p><i class="fas fa-circle-notch fa-spin" id="mainloadingicon"></i> Loading data...</p>
            </div>
            @yield('extra')
            <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="errorModalLabel">System Error</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Ops! Something went wrong... try later!</p>
                            <div class="space"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/assets/js/app.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.7/js/responsive.bootstrap4.min.js"></script>
        <script src='https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js'></script>
        <script>
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
        </script>     
        @yield('js')
    </body>
</html>