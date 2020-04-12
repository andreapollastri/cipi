<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cloud Control Panel">
    <meta name="author" content="Andrea Pollastri - andrea@pollastri.dev">
    <link rel="icon" type="image/png" href="/favicon.png">

    <title>Cipi | @yield('title')</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://allyoucan.cloud/cdn/bootstrap/core/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="https://allyoucan.cloud/cdn/fontawesome/5.11.2/css/all.css">
    <link rel='stylesheet' href='https://allyoucan.cloud/fonts/css/?family=OpenSans-Regular'>

    <style>
    html {
        font-family: OpenSans-Regular;
        position: relative;
        min-height: 100%;
    }
    body {
        margin-bottom: 60px;
    }
    .navbar {
        min-height: 75px;
    }
    .navbar-brand {
        padding: 8px;
    }
    .navbar-default .navbar-nav>.active>a, .navbar-default .navbar-nav>.active>a:focus, .navbar-default .navbar-nav>.active>a:hover {
        background: none;
    }
    .navbar-nav>.active>a {
        font-weight: bold;
    }
    .navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover {
        background: none;
    }
    .navbar-toggle {
        margin-top: 32px;
    }
    body > .container {
        margin-top: 100px;
    }
    .space {
        min-height: 25px;
    }
    .spacex {
        margin-right: 20px;
    }
    .limitch {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 50ch;
    }
    .upper {
        text-transform: uppercase;
    }
    .lower {
        text-transform: lowercase;
    }
    .green {
        color: darkolivegreen;
    }
    .red {
        color: darkred;
    }
    .dashbox {
        color: #555;
        padding: 8px 10px;
        min-height: 175px;
        border: #555 1px solid;
        margin-bottom: 25px;
    }
    .dashbox:hover {
        background: #dfdfdf;
        cursor: pointer;
    }
    .dashbox-stats {
        padding-top: 10px;
    }
    .dashbox-stats>span {
        display: block;
        font-weight: bold;
        font-size: 20px;
        min-height: 30px;
    }
    .dashbox-hr {
        border-bottom: #555 1px solid;
    }
    .dashbox-rb {
        border-right: #555 1px solid;
    }
    .copy {
        margin-top: 11px;
    }
    .copy>span {
        font-size: 11px;
        display: block;
    }
    .footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 60px;
        background-color: #f5f5f5;
    }
    @media (min-width: 768px) {
        .navbar-nav>li>a {
            padding-top: 40px;
        }
    }
    </style>
    @yield('css')
    <!-- CSS -->


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>


    <!-- NAV -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/"><img src="/logo.png"></a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li {{ request()->is('dashboard') ? 'class=active' : '' }}>
                        <a href="/dashboard"><i class="fas fa-tachometer-alt fa-fw"></i> dashboard</a>
                    </li>
                    <li {{ request()->is('cloud') ? 'class=active' : '' }}>
                        <a href="/cloud"><i class="fas fa-cloud fa-fw"></i> cloud</a>
                    </li>
                    <li {{ request()->is('apps') ? 'class=active' : '' }}>
                        <a href="/apps"><i class="fas fa-laptop-code fa-fw"></i> apps</a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i> John Frusciante <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="/me"><i class="fas fa-user-cog fa-fw"></i> profile</a></li>
                            <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt fa-fw"></i> logout
                            </a>
                            <form id="logout-form" action="/auth/logout" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- NAV -->


    <!-- MAIN -->
    <div class="container">
        <div class="row">
            @yield('content')
        </div>
    </div>
    <!-- MAIN -->


    <!-- FOOTER -->
    <div class="space"></div>
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 text-center copy">
                    <a href="https://cipi.sh/" target="_blank"><b>cipi</b></a> cloud control panel
                    <span>made with <i class="fas fa-heart"></i> by <a href="https://andreapollastri.dev" target="_blank">Andrea Pollastri</a></span>
                </div>
            </div>
        </div>
    </footer>
    <!-- FOOTER -->


    <!-- MODALS -->


    @yield('modals')
    <!-- MODALS -->


    <!-- JS -->
    <script src="https://allyoucan.cloud/cdn/jquery/core/3.4.1/jquery.js"></script>
    <script src="https://allyoucan.cloud/cdn/bootstrap/core/3.3.7/js/bootstrap.js"></script>
    <script src="https://allyoucan.cloud/cdn/webshim/1.16.0/polyfiller.js"></script>
    <script>
    (function () {
    webshim.setOptions('forms', {
        lazyCustomMessages: true,
        iVal: {
            sel: '.ws-validate',
            handleBubble: 'hide', // hide error bubble

            //add bootstrap specific classes
            errorMessageClass: 'help-block',
            successWrapperClass: 'has-success',
            errorWrapperClass: 'has-error',

            //add config to find right wrapper
            fieldWrapper: '.form-group'
        }
    });

    //load forms polyfill + iVal feature
    webshim.polyfill('forms');
    })();

    //cloud icon
    function cloudicon(provider) {
        switch (provider) {
            case 'aws':
                return '<i class="fa-fw fab fa-aws"></i><span style="display:none">'+provider+'</span>';
                break;
            case 'linode':
                return '<i class="fa-fw fab fa-linode"></i><span style="display:none">'+provider+'</span>';
                break;
            case 'do':
                return '<i class="fa-fw fab fa-digital-ocean"></i><span style="display:none">'+provider+'</span>';
                break;
            case 'google':
                return '<i class="fa-fw fab fa-google"></i><span style="display:none">'+provider+'</span>';
                break;
            case 'azure':
                return '<i class="fa-fw fab fa-microsoft"></i><span style="display:none">'+provider+'</span>';
                break;
            default:
                return '<span style="font-size:10px;font-weight:400;text-transform:capitalize;">'+provider+'</span>';
                break;
        }
    }
    </script>
    @yield('js')
    <!-- JS -->


</body>

</html>
