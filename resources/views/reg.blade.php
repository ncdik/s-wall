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

<div class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="#">Сайтсофт</a>
        <ul class="nav">
            <li><a href="#">Главная</a></li>
            <li><a href="#">Авторизация</a></li>
            <li class="active"><a href="#">Регистрация</a></li>
        </ul>

        <ul class="nav pull-right">
            <li><a>Username</a></li>
            <li><a href="#">Выход</a></li>
        </ul>
    </div>
</div>

<div class="row-fluid">
    <div class="span4"></div>
    <div class="span8">

        <form action="" method="post" class="form-horizontal">
            <div class="control-group">
                <b>Регистрация</b>
            </div>
            <div class="control-group">
                <input type="text" id="inputLogin" name="username" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off">
            </div>
            <div class="control-group error">
                <input type="password" id="inputPassword" name="password" placeholder="Пароль"
                       data-cip-id="inputPassword">
                <span class="help-inline">Текст ошибки</span>
            </div>
            <div class="control-group error">
                <input type="password" id="inputPassword2" name="password" placeholder="Повторите пароль"
                       data-cip-id="inputPassword2">
                <span class="help-inline">Текст ошибки</span>
            </div>
            <div class="control-group">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </form>
    </div>
</div>


</body>
</html>