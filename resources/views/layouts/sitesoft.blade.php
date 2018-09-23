<!DOCTYPE html>
<html>
<head>
    <title>Сайтсофт</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('js/jquery-min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/md5.js') }}"></script>
    <script src="{{ asset('js/crypt.js') }}"></script>
    <script>var WS = tstart('{{ env('WS_ADDR', 'localhost') }}', '{{ env('WS_PORT', '8000') }}');</script>
</head>
<body>

@csrf
<input type="hidden" name="_xsrf" value="@if(isset($_SERVER['HTTP_COOKIE'])){{ $_SERVER['HTTP_COOKIE'] }}@endif" />
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@auth
    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}" />
    <input type="hidden" name="user_name" value="{{ Auth::user()->name }}" />
    <input type="hidden" name="usertok" value="{{ $usertok }}" />
@else
    <input type="hidden" name="user_id" value="---" />
    <input type="hidden" name="user_name" value="---" />
    <input type="hidden" name="usertok" value="---" />
@endauth

<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="{{ route('index') }}">Сайтсофт</a>
        <ul class="nav">
            <li @yield('class_home')><a href="{{ route('index') }}">Главная</a></li>
            @guest
                <li @yield('class_auth')><a href="{{ route('login') }}">Авторизация</a></li>
                <li @yield('class_reg')><a href="{{ route('register') }}">Регистрация</a></li>
            @endguest
        </ul>

        @auth
            <ul class="nav pull-right">
                <li><a>{{ Auth::user()->name }}</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выход</a></li>
            </ul>
        @endauth
    </div>
</div>

@yield('content');

</body>
</html>