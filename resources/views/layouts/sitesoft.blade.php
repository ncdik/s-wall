<!DOCTYPE html>
<html>
<head>
    <title>Сайтсофт</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
    <script src="{{ asset('js/jquery-min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/md5.js') }}"></script>
    <script src="{{ asset('js/crypt.js') }}"></script>
</head>
<body>

@csrf
<input type="hidden" name="_xsrf" value="<?= $_SERVER['HTTP_COOKIE'] ?>" />
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

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