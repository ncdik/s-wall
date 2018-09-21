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
            <li><a href="/">Главная</a></li>
            <li class="active"><a href="#">Авторизация</a></li>
            <li><a href="#">Регистрация</a></li>
        </ul>

        <ul class="nav pull-right">
            <li><a>Username</a></li>
            <li><a href="#">Выход</a></li>
        </ul>
    </div>
</div>

<div class="row-fluid">
    <div class="span4"></div>
    <div class="span3">

        <div class="alert alert-error">
            Вход в систему с указанными данными невозможен
        </div>

        <form action="" method="post" class="form-horizontal">
            <div class="control-group">
                <b>Авторизация</b>
            </div>
            <div class="control-group">
                <input type="text" id="inputLogin" name="username" placeholder="Логин" data-cip-id="inputLogin"
                       autocomplete="off">
            </div>
            <div class="control-group">
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