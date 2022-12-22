<!DOCTYPE html>
<html lang="ru" class="no-js">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,700" rel="stylesheet">
    <link href="{{ mix('/assets/css/app.css') }}" rel="stylesheet">

    @yield('styles')
</head>
<body>

<div class="wrapper @isset($formVersion)v{{ $formVersion }} is-{{$formVersionName}}@endisset" id="top">
    @include('layouts.partials.header')

    <div class="container">
        @if($errors->any())
            <h4>{{$errors->first()}}</h4>
        @endif

        <div class="content">
            @yield('content')
        </div>
    </div>

    @section('footer')
        @include('layouts.partials.footer')
    @show
</div>

<script src="{{ mix('/assets/js/app.js' )}}"></script>

</body>
</html>
