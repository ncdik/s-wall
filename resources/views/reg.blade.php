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
        <a class="brand" href="/">Сайтсофт</a>
        <ul class="nav">
            <li><a href="/">Главная</a></li>

            @guest
                <li><a href="{{ route('login') }}">Авторизация</a></li>
                <li class="active"><a href="{{ route('register') }}">Регистрация</a></li>
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
    <div class="span8">

        <form action="{{ route('register') }}" method="post" class="form-horizontal">
            @csrf

            <div class="control-group">
                <b>Регистрация</b>
            </div>
            <div class="control-group{{ $errors->has('name') ? ' error' : '' }}">
                <input type="text" id="name" name="name" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off" value="{{ old('name') }}">
                @if ($errors->has('name'))
                    <span class="help-inline">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="control-group{{ $errors->has('email') ? ' error' : '' }}">
                <input type="email" id="inputEmail" name="email" placeholder="E-mail" data-cip-id="inputEmail"
                       autocomplete="off" value="{{ old('email') }}">
                @if ($errors->has('email'))
                    <span class="help-inline">{{ $errors->first('email') }}</span>
                @endif

            </div>

            <div class="control-group{{ $errors->has('password') ? ' error' : '' }}">
                <input type="password" id="inputPassword" name="password" placeholder="Пароль"
                       data-cip-id="inputPassword">
                @if ($errors->has('password'))
                    <span class="help-inline">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="control-group">
                <input type="password" id="password-confirm" name="password_confirmation" placeholder="Повторите пароль"
                       data-cip-id="inputPassword2">
            </div>

            <div class="control-group">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </form>
    </div>
</div>


</body>
</html>