<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('front.partials._head')
    @yield('css')
</head>
<body>

    @include('front.partials._header')

    <div>

        @yield('content')

        @include('front.partials._footer')

    </div>

    @include('front.partials._foot')
    @yield('js')
</body>
</html>
