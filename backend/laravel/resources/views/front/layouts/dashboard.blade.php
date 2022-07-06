<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('front.partials._head')
</head>
<body>

    @yield('content')

    @include('front.partials._foot')
</body>
</html>
