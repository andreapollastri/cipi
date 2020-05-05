<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Cipi') }} | @yield('title')</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://allyoucan.cloud/cdn/fontawesome/5.11.2/css/all.css">
    <link rel='stylesheet' href='https://allyoucan.cloud/fonts/css/?family=Nunito-Regular'>
    <link href="/app.css" rel="stylesheet">
    @yield('css')
</head>

<body id="page-top">
    <div id="wrapper">

        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
                <img src="/logo.png" class="img-responsive sidebar-brand-text mx-3">
            </a>
            <div class="space d-none d-md-block d-lg-block"></div>
            <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="/dashboard">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('servers') ? 'active' : '' }}">
                <a class="nav-link" href="/servers">
                    <i class="fas fa-fw fa-server"></i>
                    <span>Servers</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('applications') ? 'active' : '' }}">
                <a class="nav-link" href="/applications">
                    <i class="fas fa-fw fa-rocket"></i>
                    <span>Applications</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('aliases') ? 'active' : '' }}">
                <a class="nav-link" href="/aliases">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>Aliases</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('databases') ? 'active' : '' }}">
                <a class="nav-link" href="/databases">
                    <i class="fas fa-fw fa-database"></i>
                    <span>Databases</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('users') ? 'active' : '' }}">
                <a class="nav-link" href="/users">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('settings') ? 'active' : '' }}">
                <a class="nav-link" href="/settings">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" target="_blank" href="https://cipi.sh/docs/">
                    <i class="fas fa-fw fa-book"></i>
                    <span>Documentation</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-fw fa-power-off"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="/logout" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container float-left">
                    <div class="space"></div>
                    <h1 class="h3 mb-4 text-gray-800"><i class="fas fa-terminal"></i> @yield('title')</h1>
                </div>
                <div class="container float-left">
                    @yield('content')
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span><a href="https://cipi.sh/" target="_blank"><b>Cipi</b> - <i>Cloud Control Panel</i></a></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @yield('extra')

    <script src="https://allyoucan.cloud/cdn/jquery/core/3.4.1/jquery.js"></script>
    <script src="https://allyoucan.cloud/cdn/bootstrap/core/4.2.1/js/bootstrap.js"></script>
    <script src="https://allyoucan.cloud/cdn/webshim/1.16.0/polyfiller.js"></script>
    <script src="/app.js"></script>
    @yield('js')

</body>

</html>
