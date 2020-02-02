<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'OJT') }}</title>
    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/ico">
    <!-- Scripts -->
    <script
			  src="https://code.jquery.com/jquery-3.3.1.js"
			  integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
              crossorigin="anonymous"></script>
              <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script>
               <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.0/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
        @guest
        {{-- pass --}}
       @else
       <script>
           function replaceAll(str, find, replace) {
                return str.replace(new RegExp(find, 'g'), replace);
            }
             window.user='{{ Auth::user() }}';
             window.current_user_id='{{ Auth::user()->id }}';
             window.current_user_photo_url='{{ Auth::user()->photo_url }}';
             window.user=JSON.parse(replaceAll(window.user,'&quot;','"'))
             console.log(window.user.role)
       </script>
                
   @endguest
   

<!-- BODY options, add following classes to body to change options

    // Header options
    1. '.header-fixed'                  - Fixed Header

    // Sidebar options
    1. '.sidebar-fixed'                 - Fixed Sidebar
    2. '.sidebar-hidden'                - Hidden Sidebar
    3. '.sidebar-off-canvas'        - Off Canvas Sidebar
    4. '.sidebar-compact'               - Compact Sidebar Navigation (Only icons)

    // Aside options
    1. '.aside-menu-fixed'          - Fixed Aside Menu
    2. '.aside-menu-hidden'         - Hidden Aside Menu
    3. '.aside-menu-off-canvas' - Off Canvas Aside Menu

    // Footer options
    1. '.footer-fixed'                      - Fixed footer

    -->
</head>
<body class="app header-fixed sidebar-fixed aside-menu-fixed aside-menu-hidden">
    

        <main >
            @yield('content')
        </main>
    
    
</body>
</html>
