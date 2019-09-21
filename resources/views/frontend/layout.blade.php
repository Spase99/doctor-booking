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
        <meta name="charset" content="utf-8">
        <link href="{{ url('css/app.css') }}" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="frontend">
            <header class="main-header">
                @if (file_exists(public_path('images/logo.svg')))
                    <img class="logo" src="{{ asset('images/logo.svg') }}">
                @endif
            </header>
            <div class="container">
                @yield('content')
            </div>
        </div>
        
        <script src="{{ url('js/app.js') }}"></script>
        @stack('scripts')

    </body>

</html>
