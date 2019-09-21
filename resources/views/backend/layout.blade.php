<html>
    <head>
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicons/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('images/favicons/favicon-32x32.png') }}" sizes="32x32">
        <link rel="icon" type="image/png" href="{{ asset('images/favicons/favicon-16x16.png') }}" sizes="16x16">
        <link rel="manifest" href="{{ asset('images/favicons/manifest.json') }}">
        <link rel="mask-icon" href="{{ asset('images/favicons/safari-pinned-tab.svg') }}" color="#5bbad5">
        <meta name="theme-color" content="#ffffff">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="api-base-url" content="{{ url('api') }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="{{ url('css/app.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="wrapper">

            <!-- Navbar -->
            <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
                <!-- Sidebar - Brand -->
                <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('backend') }}">
                    <div class="sidebar-brand-icon">
                        @if (file_exists(public_path('images/logo.svg')))
                            <img class="navbar-hfl-logo" src="{{ asset('images/logo.svg') }}">
                        @endif
                    </div>
                    <div class="sidebar-brand-text mx-3">HealthForLife</div>
                </a>
            
                <hr class="sidebar-divider my-0">
            
                <!-- Doc Navigation -->
                @role('doctor')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rooms.index') }}">
                            <i class="fas fa-calendar"></i>
                            <span>Kalender</span>
                        </a>
                    </li>
                @endrole


                <!-- Admin Navigation -->
                @role('admin')
                    <li class="nav-item">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDashboard" aria-expanded="true" aria-controls="collapseTwo">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <div id="collapseDashboard" class="collapse">
                            <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item" href="{{ route('backend') }}#_rooms">Räume</a>
                            <a class="collapse-item" href="{{ route('backend') }}#_docs">Ärzte</a>
                            <a class="collapse-item" href="{{ route('backend') }}#_types">Termintypen</a>
                            <a href="{{ route('backend') }}#_blockings" class="collapse-item">Öffnungszeiten</a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rooms.index') }}">
                            <i class="fas fa-calendar"></i>
                            <span>Kalender</span>
                        </a>
                    </li>
                @endrole

                @role('reception')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('backend') }}">
                            <i class="fas fa-calendar"></i>
                            <span>Kalender</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('rooms.overdraft') }}">
                            <i class="fas fa-calendar"></i>
                            <span>Überziehung notieren</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('booking.index') }}">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Patient buchen</span>
                        </a>
                    </li>
                @endrole
                
                <hr class="sidebar-divider">
                <div class="text-center d-none d-md-inline">
                    <button class="rounded-circle border-0" id="sidebarToggle"></button>
                </div>
            </ul>

            <div id="content-wrapper">
                <div id="content">
                    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                        <!-- Sidebar Toggle (Topbar) -->
                        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                            <i class="fa fa-bars"></i>
                        </button>
        
                        <!-- Topbar Navbar -->
                        <ul class="navbar-nav ml-auto">
        
                            <div class="topbar-divider d-none d-sm-block"></div>
        
                            <!-- Nav Item - User Information -->
                            <li class="nav-item dropdown no-arrow">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{{ isset(Auth::user()->name) ? Auth::user()->name : Auth::user()->email }}}</span>
                                    <img class="img-profile rounded-circle" src="{{ asset('images/user.png') }}">
                                </a>
                                <!-- Dropdown - User Information -->
                                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                    <a class="dropdown-item" href="{{ route('doctor.profile') }}">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Profile
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ \Illuminate\Support\Facades\URL::route('logout') }}" method="post" class="mb-0">
                                        {{ csrf_field() }}
                                        <!--<button type="submit" class="btn btn-outline-danger"></button>-->
                                        <button type="submit" class="dropdown-item dropdown-logout-button">
                                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </nav>
                    @yield('content')
                </div>
            </div>

        <script src="{{ url('js/app.js') }}"></script>
        @stack('scripts')
    </body>
</html>
