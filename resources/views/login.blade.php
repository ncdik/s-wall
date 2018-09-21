<!DOCTYPE html>
<html>
<head>
    <title>Сайтсофт</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
    <script src="http://code.jquery.com/jquery.js"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</head>
<body>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="#">Сайтсофт</a>
        <ul class="nav">
            <li><a href="/">Главная</a></li>
            @guest
                <li class="active"><a href="{{ route('login') }}">Авторизация</a></li>
                <li><a href="{{ route('register') }}">Регистрация</a></li>
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

<div class="row-fluid">
    <div class="span4"></div>
    <div class="span3">

        @if ($errors->has('name') || $errors->has('password'))
            <div class="alert alert-error">
                Вход в систему с указанными данными невозможен
            </div>
        @endif

        <form action="{{ route('login') }}" method="post" class="form-horizontal">
            @csrf

            <div class="control-group">
                <b>Авторизация</b>
            </div>
            <div class="control-group{{ $errors->has('name') ? ' error' : '' }}">
                <input type="text" id="inputLogin" name="name" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off" value="{{ old('name') }}">
            </div>
            <div class="control-group{{ $errors->has('password') ? ' error' : '' }}">
                <input type="password" id="inputPassword" name="password" placeholder="Пароль"
                       data-cip-id="inputPassword">
            </div>
            <div class="control-group">
                <label class="checkbox">
                    <input type="checkbox" name="remember" value="1"> Запомнить меня
                </label>
                <button type="submit" class="btn btn-primary">Вход</button>
            </div>
        </form>
    </div>
</div>


</body>
</html>